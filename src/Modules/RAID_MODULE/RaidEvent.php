<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\Events\Event;

abstract class RaidEvent extends Event {
	public const EVENT_MASK = 'raid(*)';

	public function __construct(
		public Raid $raid,
		public string $player
	) {
	}
}
