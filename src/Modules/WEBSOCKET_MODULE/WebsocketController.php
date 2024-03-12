<?php declare(strict_types=1);

namespace Nadybot\Modules\WEBSOCKET_MODULE;

use function Safe\{json_decode};

use Amp\Http\Server\{Request, Response};
use Amp\Websocket\Server\{AllowOriginAcceptor, Websocket, WebsocketClientGateway, WebsocketClientHandler, WebsocketGateway};
use Amp\Websocket\{WebsocketClient, WebsocketMessage};
use Exception;
use Nadybot\Core\{
	Attributes as NCA,
	Channels\WebChannel,
	Event,
	EventManager,
	MessageHub,
	ModuleInstance,
	PackageEvent,
	Registry,
};
use Nadybot\Modules\WEBSERVER_MODULE\{
	CommandReplyEvent,
	JsonExporter,
	WebserverController,
};
use Psr\Log\LoggerInterface;
use Throwable;

use TypeError;

/**
 * @author Nadyita (RK5) <nadyita@hodorraid.org>
 */
#[
	NCA\Instance,
	NCA\ProvidesEvent("websocket(subscribe)"),
	NCA\ProvidesEvent("websocket(request)"),
	NCA\ProvidesEvent("websocket(response)"),
	NCA\ProvidesEvent("websocket(event)")
]
class WebsocketController extends ModuleInstance implements WebsocketClientHandler {
	/** Enable the websocket handler */
	#[NCA\Setting\Boolean]
	public bool $websocket = true;

	/** @var array<string,int> */
	protected array $clients = [];

	#[NCA\Logger]
	private LoggerInterface $logger;

	#[NCA\Inject]
	private EventManager $eventManager;

	#[NCA\Inject]
	private MessageHub $messageHub;

	#[NCA\Inject]
	private WebserverController $webserverController;

	/** @var array<int,string[]> */
	private array $subscriptions = [];

	private readonly WebsocketGateway $gateway;

	public function __construct() {
		$this->gateway = new WebsocketClientGateway();
	}

	#[NCA\Setup]
	public function setup(): void {
		if ($this->websocket) {
			$this->registerWebChat();
		}
	}

	#[NCA\SettingChangeHandler('websocket')]
	public function changeWebsocketStatus(string $setting, string $oldValue, string $newValue, mixed $extraData): void {
		if ($newValue === "1") {
			$this->registerWebChat();
		} else {
			$this->unregisterWebChat();
		}
	}

	#[
		NCA\HttpGet("/events"),
	]
	public function handleWebsocketStart(Request $request): ?Response {
		if (!$this->websocket) {
			$this->logger->notice("Websocket turned off");
			return null;
		}
		$httpServer = $this->webserverController->getServer();
		if (!isset($httpServer)) {
			$this->logger->notice("No http server found");
			return null;
		}
		$websocket = new Websocket(
			httpServer: $httpServer,
			logger: $this->logger,
			acceptor: new AllowOriginAcceptor([$request->getHeader('origin') ?? 'http://127.0.0.1:8080']),
			clientHandler: $this,
		);
		$this->logger->notice("Passing control to Websocket");
		return $websocket->handleRequest($request);
	}

	public function handleClient(WebsocketClient $client, Request $request, Response $response): void {
		$this->logger->notice("New Websocket connection from {peer}", [
			"peer" => $client->getRemoteAddress(),
		]);
		$this->gateway->addClient($client);
		$this->subscriptions[$client->getId()] = [];
		$packet = new WebsocketCommand();
		$packet->command = "uuid";
		$packet->data = (string)$client->getId();
		$client->sendText(JsonExporter::encode($packet));
		while (null !== ($msg = $client->receive())) {
			try {
				$this->handleIncomingMessage($client, $msg);
			} catch (Throwable $e) {
				unset($this->subscriptions[$client->getId()]);
			}
		}
		unset($this->subscriptions[$client->getId()]);
	}

	#[NCA\Event(
		name: "websocket(subscribe)",
		description: "Handle Websocket event subscriptions",
		defaultStatus: 1
	)]
	public function handleSubscriptions(WebsocketSubscribeEvent $event, WebsocketClient $client): void {
		try {
			if (!isset($event->data->events) || !is_array($event->data->events)) {
				return;
			}
			$this->subscriptions[$client->getId()] = $event->data->events;
			$this->logger->info('Websocket subscribed to ' . join(",", $event->data->events));
		} catch (TypeError) {
			$client->close(4002);
		}
	}

	#[NCA\Event(
		name: "websocket(request)",
		description: "Handle API requests"
	)]
	public function handleRequests(WebsocketRequestEvent $event, WebsocketClient $client): void {
		// Not implemented yet
	}

	#[NCA\Event(
		name: "*",
		description: "Distribute events to Websocket clients",
		defaultStatus: 1
	)]
	public function displayEvent(Event $event): void {
		$isPrivatPacket = $event->type === 'msg'
			|| $event instanceof PackageEvent
			|| $event instanceof WebsocketEvent;
		// Packages that might contain secret or private information must never be relayed
		if ($isPrivatPacket) {
			return;
		}
		$packet = new WebsocketCommand();
		$packet->command = $packet::EVENT;
		$packet->data = $event;
		foreach ($this->subscriptions as $id => $subscriptions) {
			if ($event instanceof CommandReplyEvent && $event->uuid !== (string)$id) {
				continue;
			}
			foreach ($subscriptions as $subscription) {
				if ($subscription === $event->type
					|| fnmatch($subscription, $event->type)) {
					$this->gateway->sendText(JsonExporter::encode($packet), $id);
					$this->logger->info("Sending {class} to Websocket client", [
						"class" => get_class($event),
						"packet" => $packet,
					]);
				}
			}
		}
	}

	/** Check if a Websocket client connection exists */
	public function clientExists(string $uuid): bool {
		return isset($this->subscriptions[$uuid]);
	}

	protected function registerWebChat(): void {
		$wc = new WebChannel();
		Registry::injectDependencies($wc);
		$this->messageHub
			->registerMessageEmitter($wc)
			->registerMessageReceiver($wc);
	}

	protected function unregisterWebChat(): void {
		$wc = new WebChannel();
		Registry::injectDependencies($wc);
		$this->messageHub
			->unregisterMessageEmitter($wc->getChannelName())
			->unregisterMessageReceiver($wc->getChannelName());
	}

	private function handleIncomingMessage(WebsocketClient $client, WebsocketMessage $message): void {
		$body = $message->buffer();
		$this->logger->notice("[Data inc.] {data}", ["data" => $body]);
		try {
			if (!is_string($body)) {
				throw new Exception();
			}
			$data = json_decode($body);
			$command = new WebsocketCommand();
			$command->fromJSON($data);
			if (!in_array($command->command, $command::ALLOWED_COMMANDS)) {
				throw new Exception();
			}
		} catch (Throwable) {
			$client->close(4002);
			return;
		}
		if ($command->command === $command::SUBSCRIBE) {
			$newEvent = new WebsocketSubscribeEvent();
			$newEvent->type = "websocket(subscribe)";
			$newEvent->data = new NadySubscribe();
		} elseif ($command->command === $command::REQUEST) {
			$newEvent = new WebsocketRequestEvent();
			$newEvent->type = "websocket(request)";
			$newEvent->data = new NadyRequest();
		} else {
			// Unknown command received is just silently ignored in case another handler deals with it
			return;
		}
		try {
			if (!is_object($command->data)) {
				throw new Exception("Invalid data received");
			}
			$newEvent->data->fromJSON($command->data);
		} catch (Throwable) {
			$client->close(4002);
			return;
		}
		$this->eventManager->fireEvent($newEvent, $client);
	}
}
