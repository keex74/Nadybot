<?php declare(strict_types=1);

namespace Nadybot\Modules\ALIEN_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'alienweapons', shared: Shared::Yes)]
class AlienWeapon extends DBTable {
	public function __construct(
		public int $type=0,
		public string $name='',
	) {
	}
}
