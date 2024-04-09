<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'buffs', shared: Shared::Yes)]
class Buff extends DBTable {
	public function __construct(
		#[PK] public int $id,
		public int $nano_id,
		public ?int $disc_id,
		public ?int $use_id,
		public string $name,
		public int $ncu,
		public int $nanocost,
		public int $school,
		public int $strain,
		public int $duration,
		public int $attack,
		public int $recharge,
		public int $range,
		public int $initskill,
	) {
	}
}
