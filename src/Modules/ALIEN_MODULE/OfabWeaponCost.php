<?php declare(strict_types=1);

namespace Nadybot\Modules\ALIEN_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'ofabweaponscost', shared: Shared::Yes)]
class OfabWeaponCost extends DBTable {
	public function __construct(
		public int $ql,
		public int $vp,
	) {
	}
}
