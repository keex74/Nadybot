<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'raid_block')]
class RaidBlock extends DBRow {
	public int $time;

	public function __construct(
		#[PK] public string $player,
		#[PK] public string $blocked_from,
		public string $blocked_by,
		public string $reason,
		?int $time=null,
		public ?int $expiration=null,
	) {
		$this->time = $time ?? time();
	}
}
