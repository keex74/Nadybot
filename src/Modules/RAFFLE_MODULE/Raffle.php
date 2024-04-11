<?php declare(strict_types=1);

namespace Nadybot\Modules\RAFFLE_MODULE;

use function Safe\preg_split;
use Nadybot\Core\{CommandReply, Safe};

class Raffle {
	/** Timestamp when the raffle was started*/
	public int $start;

	/** Unix timestamp when the raffle was announced the last time */
	public ?int $lastAnnounce = null;

	/**
	 * @param CommandReply $sendto           Where to send announcements, etc. to
	 * @param string       $raffler          Name of the character giving away items
	 * @param RaffleSlot[] $slots
	 * @param ?int         $start            Timestamp when the raffle was started
	 * @param ?int         $end              If set, this is the unix timestamp when the raffle will end
	 * @param ?int         $announceInterval Interval (in second) between 2 announcements
	 * @param bool         $allowMultiJoin   Allow someone to join for more than 1 item at a time
	 */
	public function __construct(
		public CommandReply $sendto,
		public string $raffler,
		public array $slots=[],
		?int $start=null,
		public ?int $end=null,
		public ?int $announceInterval=null,
		public bool $allowMultiJoin=true,
	) {
		$this->start = $this->lastAnnounce = $start ?? time();
	}

	public function toString(string $prefix=''): string {
		$list = $this->toList();
		$items = [];
		for ($i = 0; $i < count($list); $i++) {
			$items []= ((count($list) > 1) ? 'Item ' . ($i + 1) . ': ' : '') . "<highlight>{$list[$i]}<end>";
		}
		return $prefix . implode("\n{$prefix}", $items);
	}

	public function fromString(string $text): void {
		$text = Safe::pregReplace("/>\s*</", '>,<', $text);
		// Items with "," in their name get this escaped
		$text = preg_replace_callback(
			"/(['\"]?itemref:\/\/\d+\/\d+\/\d+['\"]?>)(.+?)(<\/a>)/",
			static function (array $matches): string {
				return $matches[1] .  str_replace(',', '&#44;', $matches[2]) . $matches[3];
			},
			$text
		);
		$parts = preg_split("/\s*,\s*/", $text);
		foreach ($parts as $part) {
			$slot = new RaffleSlot();
			$slot->fromString($part);
			$this->slots []= $slot;
		}
	}

	/** @return string[] */
	public function toList(): array {
		$slots = [];
		foreach ($this->slots as $slot) {
			$slots []= $slot->toString();
		}
		return $slots;
	}

	/** @return string[] */
	public function getParticipantNames(): array {
		return array_reduce(
			$this->slots,
			static function (array $carry, RaffleSlot $slot): array {
				return array_unique([...$carry, ...$slot->participants]);
			},
			[]
		);
	}

	public function isInRaffle(string $player, ?int $slot=null): ?bool {
		if ($slot !== null) {
			if (!isset($this->slots[$slot])) {
				return null;
			}
			$participants = $this->slots[$slot]->participants;
		} else {
			$participants = $this->getParticipantNames();
		}
		return in_array($player, $participants);
	}

	/** @return string[] */
	public function getWinnerNames(): array {
		/** @var string[][] */
		$winners = array_map(
			static function (RaffleSlot $slot): array {
				return $slot->getWinnerNames();
			},
			$this->slots
		);

		/** @psalm-suppress NamedArgumentNotAllowed */
		return array_merge(...$winners);
	}
}
