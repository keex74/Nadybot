<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE\RelayProtocol;

use function Safe\preg_match;
use Nadybot\Core\{
	Attributes as NCA,
	Routing\Character,
	Routing\Events\Base,
	Routing\RoutableEvent,
	Routing\RoutableMessage,
	Routing\Source,
	Safe,
	Text,
	Util,
};

use Nadybot\Modules\RELAY_MODULE\{
	Relay,
	RelayMessage,
};

#[
	NCA\RelayProtocol(
		name: 'grcv2',
		description: "This is the old Nadybot protocol.\n".
			"It enhances the old grc protocol by adding descriptions\n".
			"in front of the tags and messages, so the client-side\n".
			"can decide how to colorize them. However, it only supports\n".
			'org, guest and raidbot chat.'
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
	)
]
class GrcV2Protocol implements RelayProtocolInterface {
	protected static int $supportedFeatures = self::F_NONE;

	protected Relay $relay;

	protected string $command = 'grc';
	protected string $prefix = '';

	#[NCA\Inject]
	private Text $text;

	public function __construct(string $command='grc', string $prefix='') {
		$this->command = $command;
		$this->prefix = $prefix;
	}

	public function send(RoutableEvent $event): array {
		if ($event->getType() !== RoutableEvent::TYPE_MESSAGE) {
			if (!isset($event->data) || !($event->data instanceof Base) || !strlen($event->data->message??'')) {
				return [];
			}
			$event2 = clone $event;
			$event2->setData($event->data->message);
			$event = $event2;
		}
		$path = $event->getPath();
		$msgColor = '';
		$hops = [];
		$lastHop = null;
		foreach ($path as $hop) {
			$tag = $hop->render($lastHop);
			if (!isset($tag)) {
				continue;
			}
			if ($hop->type === Source::ORG) {
				$hops []= "<relay_guild_tag_color>[{$tag}]</end>";
				$msgColor = '<relay_guild_color>';
			} elseif ($hop->type === Source::PRIV) {
				if (count($hops)) {
					$hops []= "<relay_guest_tag_color>[{$tag}]</end>";
					$msgColor = '<relay_guest_color>';
				} else {
					$hops []= "<relay_raidbot_tag_color>[{$tag}]</end>";
					$msgColor = '<relay_raidbot_color>';
				}
			} else {
				$hops []= "<relay_guest_tag_color>[{$tag}]</end>";
				$msgColor = '<relay_guest_color>';
			}
		}
		$senderLink = '';
		$character = $event->getCharacter();
		if (isset($character) && Util::isValidSender($character->name)) {
			$senderLink = Text::makeUserlink($character->name) . ': ';
		} else {
			$msgColor = '<relay_bot_color>';
		}
		return [
			"{$this->prefix}{$this->command} <v2>".
				implode(' ', $hops) . " {$senderLink}{$msgColor}".
				$this->text->formatMessage($event->getData()) . '</end>',
		];
	}

	public function receive(RelayMessage $message): ?RoutableEvent {
		if (!count($message->packages)) {
			return null;
		}
		$data = array_shift($message->packages);
		$command = preg_quote($this->command, '/');
		if (!count($matches = Safe::pregMatch("/^.?{$command} <v2>(.+)/s", $data))) {
			return null;
		}
		$data = $matches[1];
		$message = new RoutableMessage($data);
		while (count($matches = Safe::pregMatch("/^<relay_(.+?)_tag_color>\[(.*?)\]<\/end>\s*(.*)/s", $data))) {
			if (strlen($matches[2])) {
				$type = ($matches[1] === 'guild') ? Source::ORG : Source::PRIV;
				$message->appendPath(new Source($type, $matches[2], $matches[2]));
			}
			$data = $matches[3];
		}
		if (count($matches = Safe::pregMatch("/^<a href=user:\/\/(.+?)>.*?<\/a>\s*:?\s*(.*)/s", $data))) {
			$message->setCharacter(new Character($matches[1]));
			$data = $matches[2];
		} elseif (count($matches = Safe::pregMatch("/^([^ :]+):\s*(.*)/s", $data))) {
			$message->setCharacter(new Character($matches[1]));
			$data = $matches[2];
		}
		if (preg_match('/^<relay_bot_color>/s', $data)) {
			$message->char = null;
		}

		$data = Safe::pregReplace('/^<relay_[a-z]+_color>(.*)$/s', '$1', $data);
		$data = Safe::pregReplace("/<\/end>$/s", '', $data);
		$message->setData(ltrim($data));
		return $message;
	}

	public function setRelay(Relay $relay): void {
		$this->relay = $relay;
	}

	public function init(callable $callback): array {
		$callback();
		return [];
	}

	public function deinit(callable $callback): array {
		$callback();
		return [];
	}

	public static function supportsFeature(int $feature): bool {
		return (static::$supportedFeatures & $feature) === $feature;
	}
}
