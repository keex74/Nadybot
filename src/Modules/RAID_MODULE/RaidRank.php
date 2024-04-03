<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'raid_rank')]
class RaidRank extends DBRow {
	public function __construct(
		#[PK] public string $name,
		public int $rank,
	) {
	}
}
