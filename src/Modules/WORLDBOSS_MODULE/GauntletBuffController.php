<?php declare(strict_types=1);

namespace Nadybot\Modules\WORLDBOSS_MODULE;

use DateTime;
use Exception;
use JsonException;
use Nadybot\Core\{
	AOChatEvent,
	CmdContext,
	EventManager,
	Http,
	HttpResponse,
	LoggerWrapper,
	MessageEmitter,
	MessageHub,
	Modules\ALTS\AltsController,
	Nadybot,
	Routing\RoutableMessage,
	Routing\Source,
	SettingManager,
	Text,
	UserStateEvent,
	Util,
};
use Nadybot\Core\ParamClass\PDuration;
use Nadybot\Modules\TIMERS_MODULE\Alert;
use Nadybot\Modules\TIMERS_MODULE\Timer;
use Nadybot\Modules\TIMERS_MODULE\TimerController;

/**
 * @author Equi
 * @author Nadyita (RK5) <nadyita@hodorraid.org>
 * @Instance
 *
 * Commands this controller contains:
 *	@DefineCommand(
 *		command     = 'gaubuff',
 *		accessLevel = 'all',
 *		description = 'Handles timer for gauntlet buff',
 *		help        = 'gaubuff.txt'
 *	)
 *	@ProvidesEvent(value="sync(gaubuff)", desc="Triggered when someone sets the gauntlet buff for either side")
 */
class GauntletBuffController implements MessageEmitter {
	public const SIDE_NONE = 'none';
	public const GAUNTLET_API = "https://timers.aobots.org/api/v1.0/gaubuffs";

	/**
	 * Name of the module.
	 * Set automatically by module loader.
	 */
	public string $moduleName;

	/** @Inject */
	public Text $text;

	/** @Inject */
	public SettingManager $settingManager;

	/** @Inject */
	public MessageHub $messageHub;

	/** @Inject */
	public Nadybot $chatBot;

	/** @Inject */
	public EventManager $eventManager;

	/** @Inject */
	public Http $http;

	/** @Inject */
	public Util $util;

	/** @Inject */
	public AltsController $altsController;

	/** @Logger */
	public LoggerWrapper $logger;

	/** @Inject */
	public TimerController $timerController;

	public function getChannelName(): string {
		return Source::SYSTEM . "(gauntlet-buff)";
	}

	/**
	 * @Setup
	 * This handler is called on bot startup.
	 */
	public function setup(): void {
		$this->settingManager->add(
			$this->moduleName,
			'gaubuff_times',
			'Times to display gaubuff timer alerts',
			'edit',
			'text',
			'30m 10m',
			'30m 10m',
			'',
			'mod',
			'gau_times.txt'
		);
		$this->settingManager->add(
			$this->moduleName,
			"gaubuff_logon",
			"Show gaubuff timer on logon",
			"edit",
			"options",
			"1",
			"true;false",
			"1;0"
		);
		$this->settingManager->add(
			$this->moduleName,
			"gaubuff_default_side",
			"Gauntlet buff side if none specified for gaubuff",
			"edit",
			"options",
			"none",
			"none;clan;omni"
		);
		$this->messageHub->registerMessageEmitter($this);
		$this->settingManager->registerChangeListener(
			"gaubuff_times",
			[$this, "validateGaubuffTimes"]
		);
	}

	/**
	 * @Event("connect")
	 * @Description("Get active Gauntlet buffs from API")
	 */
	public function loadGauntletBuffsFromAPI(): void {
		$this->http->get(static::GAUNTLET_API)
			->withCallback([$this, "handleGauntletBuffsFromApi"]);
	}

	/**
	 * Parse the Gauntlet buff timer API result and handle each running buff
	 */
	public function handleGauntletBuffsFromApi(HttpResponse $response): void {
		if ($response->headers["status-code"] !== "200" || !isset($response->body)) {
			$this->logger->log('ERROR', 'Gauntlet buff API did not send correct data.');
			return;
		}
		/** @var ApiGauntletBuff[] */
		$buffs = [];
		try {
			$data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
			if (!is_array($data)) {
				throw new JsonException();
			}
			foreach ($data as $gauntletData) {
				$buffs []= new ApiGauntletBuff($gauntletData);
			}
		} catch (JsonException $e) {
			$this->logger->log('ERROR', "Gauntlet buff API sent invalid json.");
			return;
		}
		foreach ($buffs as $buff) {
			$this->handleApiGauntletBuff($buff);
		}
	}

	/**
	 * Check if the given Gauntlet buff is valid and set or update a timer for it
	 */
	protected function handleApiGauntletBuff(ApiGauntletBuff $buff): void {
		$this->logger->log('DEBUG', "Received gauntlet information for {$buff->faction}.");
		if (!in_array(strtolower($buff->faction), ["omni", "clan"])) {
			$this->logger->log('WARN', "Received timer information for unknown faction {$buff->faction}.");
			return;
		}
		if ($buff->expires < time()) {
			$this->logger->log('WARN', "Received expired timer information for {$buff->faction} Gauntlet buff.");
			return;
		}
		$timer = $this->timerController->get("Gaubuff_{$buff->faction}");
		if (isset($timer) && abs($buff->expires-($timer->endtime??0)) < 10) {
			$this->logger->log(
				'DEBUG',
				"Already existing {$buff->faction} buff recent enough. Difference: ".
				abs($buff->expires-($timer->endtime??0)) . "s"
			);
			return;
		}
		$this->logger->log("DEBUG", "Updating {$buff->faction} buff from API");
		$this->setGaubuff(
			strtolower($buff->faction),
			$buff->expires,
			$this->chatBot->char->name,
			time()
		);
	}

	public function validateGaubuffTimes(string $setting, string $old, string $new): void {
		$lastTime = null;
		foreach (explode(' ', $new) as $utime) {
			$secs = $this->util->parseTime($utime);
			if ($secs === 0) {
				throw new Exception("<highlight>{$new}<end> is not a list of budatimes.");
			}
			if (isset($lastTime) && $secs >= $lastTime) {
				throw new Exception("You have to give notification times in descending order.");
			}
			$lastTime = $secs;
		}
	}

	private function tmTime(int $time): string {
		$gtime = new DateTime();
		$gtime->setTimestamp($time);
		return $gtime->format("D, H:i T (d-M-Y)");
	}

	public function setGaubuff(string $side, int $time, string $creator, int $createtime): void {
		$alerts = [];
		$alertTimes = [];
		$gaubuffTimes = $this->settingManager->getString('gaubuff_times');
		if (isset($gaubuffTimes)) {
			foreach (explode(' ', $gaubuffTimes) as $utime) {
				$alertTimes [] = $this->util->parseTime($utime);
			}
		}
		$alertTimes []= 0; //timer runs out
		foreach ($alertTimes as $alertTime) {
			if (($time - $alertTime) > time()) {
				$alert = new Alert();
				$alert->time = $time - $alertTime;
				$alert->message = "<{$side}>" . ucfirst($side) . "<end> Gauntlet buff ";
				if ($alertTime === 0) {
					$alert->message .= "<highlight>expired<end>.";
				} else {
					$alert->message .= "runs out in <highlight>".
						$this->util->unixtimeToReadable($alertTime)."<end>.";
				}
				$alerts []= $alert;
			}
		}
		$data = [];
		$data['createtime'] = $createtime;
		$data['creator'] = $creator;
		$data['repeat'] = 0;

		$this->timerController->remove("Gaubuff_{$side}");
		$this->timerController->add(
			"Gaubuff_{$side}",
			$this->chatBot->char->name,
			"",
			$alerts,
			"GauntletBuffController.gaubuffcallback",
			json_encode($data)
		);
	}

	public function gaubuffcallback(Timer $timer, Alert $alert): void {
		$rMsg = new RoutableMessage($alert->message);
		$rMsg->appendPath(new Source(
			Source::SYSTEM,
			"gauntlet-buff"
		));
		$this->messageHub->handle($rMsg);
	}

	protected function showGauntletBuff(string $sender): void {
		$sides = $this->getSidesToShowBuff();
		$msgs = [];
		foreach ($sides as $side) {
			$timer = $this->timerController->get("Gaubuff_{$side}");
			if ($timer === null || !isset($timer->endtime)) {
				continue;
			}
			$msgs []= "<{$side}>" . ucfirst($side) . " Gauntlet buff<end> ".
					"runs out in <highlight>".
					$this->util->unixtimeToReadable($timer->endtime - time()).
					"<end>.";
		}
		if (empty($msgs)) {
			return;
		}
		$this->chatBot->sendMassTell(join("\n", $msgs), $sender);
	}

	/**
	 * @Event("logOn")
	 * @Description("Sends gaubuff message on logon")
	 */
	public function gaubufflogonEvent(UserStateEvent $eventObj): void {
		$sender = $eventObj->sender;
		if (!$this->chatBot->isReady()
			|| !is_string($sender)
			|| (!isset($this->chatBot->guildmembers[$sender]))
			|| (!$this->settingManager->getBool('gaubuff_logon'))) {
			return;
		}
		$this->showGauntletBuff($sender);
	}

	/**
	 * @Event("joinPriv")
	 * @Description("Sends gaubuff message on join")
	 */
	public function privateChannelJoinEvent(AOChatEvent $eventObj): void {
		$sender = $eventObj->sender;
		if ($this->settingManager->getBool('gaubuff_logon') && is_string($sender)) {
			$this->showGauntletBuff($sender);
		}
	}

	/**
	 * This command handler shows when all known gauntlet buffs
	 *
	 * @HandlesCommand("gaubuff")
	 * @Mask $buffSide (clan|omni)
	 */
	public function gaubuffCommand(CmdContext $context, ?string $buffSide): void {
		$sides = $this->getSidesToShowBuff($buffSide);
		$msgs = [];
		foreach ($sides as $side) {
			$timer = $this->timerController->get("Gaubuff_{$side}");
			if ($timer !== null && isset($timer->endtime)) {
				$gaubuff = $timer->endtime - time();
				$msgs []= "<{$side}>" . ucfirst($side) . " Gauntlet buff<end> runs out ".
					"in <highlight>".$this->util->unixtimeToReadable($gaubuff)."<end>.";
			}
		}
		if (empty($msgs)) {
			if (count($sides) === 1) {
				$context->reply("No <{$sides[0]}>{$sides[0]} Gauntlet buff<end> available.");
			} else {
				$context->reply("No Gauntlet buff available for either side.");
			}
			return;
		}
		$context->reply(join("\n", $msgs));
	}

	/**
	 * This command sets a gauntlet buff timer
	 *
	 * @HandlesCommand("gaubuff")
	 * @Mask $side (clan|omni)
	 */
	public function gaubuffSetCommand(CmdContext $context, ?string $side, PDuration $time): void {
		$defaultSide = $this->settingManager->getString('gaubuff_default_side') ?? "none";
		$side = $side ?? $defaultSide;
		if ($side === static::SIDE_NONE) {
			$msg = "You have to specify for which side the buff is: omni or clan";
			$context->reply($msg);
			return;
		}
		$buffEnds = $time->toSecs();
		if ($buffEnds < 1) {
			$msg = "<highlight>" . $time() . "<end> is not a valid budatime string.";
			$context->reply($msg);
			return;
		}
		$buffEnds += time();
		$this->setGaubuff($side, $buffEnds, $context->char->name, time());
		$msg = "Gauntletbuff timer for <{$side}>{$side}<end> has been set and expires at <highlight>".$this->tmTime($buffEnds)."<end>.";
		$context->reply($msg);
		$event = new SyncGaubuffEvent();
		$event->expires = $buffEnds;
		$event->faction = strtolower($side);
		$event->sender = $context->char->name;
		$event->forceSync = $context->forceSync;
		$this->eventManager->fireEvent($event);
	}

	/**
	 * @Event("sync(gaubuff)")
	 * @Description("Sync external gauntlet buff events")
	 */
	public function syncExtGaubuff(SyncGaubuffEvent $event): void {
		if ($event->isLocal()) {
			return;
		}
		$this->setGaubuff($event->faction, $event->expires, $event->sender, time());
		$msg = "Gauntletbuff timer for <{$event->faction}>{$event->faction}<end> has ".
			"been set and expires at <highlight>" . $this->tmTime($event->expires).
			"<end>.";
		$rMsg = new RoutableMessage($msg);
		$rMsg->appendPath(new Source(
			Source::SYSTEM,
			"gauntlet-buff"
		));
		$this->messageHub->handle($rMsg);
	}

	/**
	 * Get a list of array for which to show the gauntlet buff(s)
	 * @return string[]
	 */
	protected function getSidesToShowBuff(?string $side=null): array {
		$defaultSide = $this->settingManager->getString('gaubuff_default_side') ?? "none";
		$side ??= $defaultSide;
		if ($side === static::SIDE_NONE) {
			return ['clan', 'omni'];
		}
		return [$side];
	}

	/**
	 * @NewsTile("gauntlet-buff")
	 * @Description("Show the remaining time of the currently popped Gauntlet buff(s) - if any")
	 * @Example("<header2>Gauntlet buff<end>
	 * <tab><omni>Omni Gauntlet buff<end> runs out in <highlight>4 hrs 59 mins 31 secs<end>.")
	 */
	public function gauntletBuffNewsTile(string $sender, callable $callback): void {
		$buffLine = $this->getGauntletBuffLine();
		if (isset($buffLine)) {
			$buffLine = "<header2>Gauntlet buff<end>\n{$buffLine}";
		}
		$callback($buffLine);
	}

	public function getGauntletBuffLine(): ?string {
		$defaultSide = $this->settingManager->getString('gaubuff_default_side');
		$sides = $this->getSidesToShowBuff(($defaultSide === "none") ? null : $defaultSide);
		$msgs = [];
		foreach ($sides as $side) {
			$timer = $this->timerController->get("Gaubuff_{$side}");
			if ($timer !== null && isset($timer->endtime)) {
				$gaubuff = $timer->endtime - time();
				$msgs []= "<tab><{$side}>" . ucfirst($side) . " Gauntlet buff<end> runs out ".
					"in <highlight>".$this->util->unixtimeToReadable($gaubuff)."<end>.\n";
			}
		}
		if (empty($msgs)) {
			return null;
		}
		return join("", $msgs);
	}
}