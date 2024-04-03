<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBRow;

#[Table(name: 'raid_block')]
class RaidBlock extends DBRow {
	public string $player;
	public string $blocked_from;
	public string $blocked_by;
	public string $reason;
	public int $time;
	public ?int $expiration;
}
