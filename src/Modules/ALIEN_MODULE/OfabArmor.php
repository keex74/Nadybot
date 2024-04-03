<?php declare(strict_types=1);

namespace Nadybot\Modules\ALIEN_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\{DBRow, Profession};

#[Table(name: 'ofabarmor', shared: Shared::Yes)]
class OfabArmor extends DBRow {
	public function __construct(
		public Profession $profession,
		public string $name,
		public string $slot,
		public int $lowid,
		public int $highid,
		public int $upgrade,
	) {
	}
}
