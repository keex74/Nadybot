<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'raid_points')]
class RaidPoints extends DBTable {
	public function __construct(
		#[PK] public string $username,
		public int $points,
	) {
	}
}
