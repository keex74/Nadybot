<?php declare(strict_types=1);

namespace Nadybot\Modules\RECIPE_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'arulsaba_buffs', shared: Shared::Yes)]
class ArulSabaBuffs extends DBTable {
	public function __construct(
		#[PK] public string $name,
		#[PK] public int $min_level,
		public int $left_aoid,
		public int $right_aoid,
	) {
	}
}
