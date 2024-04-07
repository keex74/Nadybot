<?php declare(strict_types=1);

namespace Nadybot\Modules\ALIEN_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'alienweaponspecials', shared: Shared::Yes)]
class AlienWeaponSpecials extends DBRow {
	public function __construct(
		#[PK] public int $type=0,
		public string $specials='',
	) {
	}
}
