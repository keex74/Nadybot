<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE\RelayProtocol;

use Nadybot\Core\{
	Attributes as NCA,
	MessageHub,
	Routing\Character,
	Routing\Events\Base,
	Routing\RoutableEvent,
	Routing\RoutableMessage,
	Routing\Source,
	Safe,
	Text,
};
use Nadybot\Modules\RELAY_MODULE\{
	Relay,
	RelayMessage,
};

use Psr\Log\LoggerInterface;

#[
	NCA\RelayProtocol(
		name: 'agcr',
		description: "This is the protocol that is used by the alliance of Rimor.\n".
			"It does not supports sharing online lists and can only colorize\n".
			'org and guest chat properly.'
	),
	NCA\Param(
		name: 'command',
		type: 'string',
		description: 'The command we send with each packet',
		required: false
	),
	NCA\Param(
		name: 'prefix',
		type: 'string',
		description: 'The prefix we send with each packet, e.g. "!" or ""',
		required: false
	),
	NCA\Param(
		name: 'force-single-hop',
		type: 'boolean',
		description: "Instead of sending \"[Org] [Guest]\", force sending \"[Org Guest]\".\n".
			"This might be needed when old bots have problems parsing your sent messages,\n".
			'because they do not support guest chats.',
		required: false
	),
	NCA\Param(
		name: 'send-user-links',
		type: 'boolean',
		description: "Send a clickable username for the sender.\n".
			'Disable when other bots cannot parse this and will render your messages wrong.',
		required: false
	)
]
class AgcrProtocol implements RelayProtocolInterface {
	protected static int $supportedFeatures = self::F_NONE;

	protected Relay $relay;

	protected string $command = 'agcr';
	protected string $prefix = '!';
	protected bool $forceSingleHop = false;
	protected bool $sendUserLinks = true;

	#[NCA\Logger]
	private LoggerInterface $logger;

	#[NCA\Inject]
	private MessageHub $messageHub;

	#[NCA\Inject]
	private Text $text;

	public function __construct(string $command='agcr', string $prefix='!', bool $forceSingleHop=false, bool $sendUserLinks=true) {
		$this->command = $command;
		$this->prefix = $prefix;
		$this->forceSingleHop = $forceSingleHop;
		$this->sendUserLinks = $sendUserLinks;
	}

	public function send(RoutableEvent $event): array {
		$this->logger->debug('Relay {relay} received event to route', [
			'relay' => $this->relay->getName(),
			'event' => $event,
		]);
		if ($event->getType() === RoutableEvent::TYPE_MESSAGE) {
			$packages = $this->renderMessage($event);
			$this->logger->debug('Event encoded successfully on {relay}', [
				'relay' => $this->relay->getName(),
				'packages' => $packages,
			]);
			return $packages;
		}
		if ($event->getType() === RoutableEvent::TYPE_EVENT) {
			if (!isset($event->data) || !($event->data instanceof Base) || !strlen($event->data->message??'')) {
				return [];
			}
			$event2 = clone $event;
			$event2->setData($event->data->message);
			$packages = $this->renderMessage($event2);
			$this->logger->debug('Event encoded successfully on {relay}', [
				'relay' => $this->relay->getName(),
				'packages' => $packages,
			]);
			return $packages;
		}
		$this->logger->debug('Relay {relay} dropped agcr packet', [
			'relay' => $this->relay->getName(),
		]);
		return [];
	}

	/** @return list<string> */
	public function renderMessage(RoutableEvent $event): array {
		$path = $this->messageHub->renderPath($event, 'relay', false, $this->sendUserLinks);
		if ($this->forceSingleHop) {
			$path = implode(' ', explode('] [', $path));
		}
		return [
			$this->prefix.$this->command . ' '.
				$path.
				$this->text->formatMessage($event->getData()),
		];
	}

	public function receive(RelayMessage $message): ?RoutableEvent {
		$this->logger->debug('Relay {relay} received message to route', [
			'relay' => $this->relay->getName(),
			'message' => $message,
		]);
		if (!count($message->packages)) {
			return null;
		}
		$command = preg_quote($this->command, '/');
		$data = array_shift($message->packages);
		if (!count($matches = Safe::pregMatch("/^.?{$command}\s+(.+)/s", $data))) {
			$this->logger->debug('Relay {relay} dropped message that was not a command', [
				'relay' => $this->relay->getName(),
			]);
			return null;
		}
		$data = $matches[1];
		$message = new RoutableMessage($data);
		if (count($matches = Safe::pregMatch("/^\[(.+?)\]\s*(.*)/s", $data))) {
			$message->appendPath(new Source(Source::ORG, $matches[1], $matches[1]));
			$data = $matches[2];
		}
		if (count($matches = Safe::pregMatch("/^\[(.+?)\]\s*(.*)/s", $data))) {
			$message->appendPath(new Source(Source::PRIV, $matches[1], $matches[1]));
			$data = $matches[2];
		}
		if (count($matches = Safe::pregMatch("/^<a href=user:\/\/(.+?)>.*?<\/a>\s*:?\s*(.*)/s", $data))) {
			$message->setCharacter(new Character($matches[1]));
			$data = $matches[2];
		} elseif (count($matches = Safe::pregMatch("/^([^ :]+):\s*(.*)/s", $data))) {
			$message->setCharacter(new Character($matches[1]));
			$data = $matches[2];
		}
		$message->setData($data);
		$this->logger->debug('Relay {relay} decoded agcr message successfully', [
			'relay' => $this->relay->getName(),
			'message' => $message,
		]);
		return $message;
	}

	public function init(callable $callback): array {
		$callback();
		return [];
	}

	public function deinit(callable $callback): array {
		$callback();
		return [];
	}

	public function setRelay(Relay $relay): void {
		$this->relay = $relay;
	}

	public static function supportsFeature(int $feature): bool {
		return (static::$supportedFeatures & $feature) === $feature;
	}
}
