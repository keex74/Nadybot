<?php declare(strict_types=1);

namespace Nadybot\Modules\ALIEN_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'alienweapons', shared: Shared::Yes)]
class AlienWeapon extends DBRow {
	public function __construct(
		public int $type=0,
		public string $name='',
	) {
	}
}
