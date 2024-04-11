<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

class RaidLockEvent extends RaidEvent {
	public const EVENT_MASK = 'raid(lock)';

	public function __construct(
		Raid $raid,
		string $player,
	) {
		parent::__construct(raid: $raid, player: $player);
		$this->type = self::EVENT_MASK;
	}
}
