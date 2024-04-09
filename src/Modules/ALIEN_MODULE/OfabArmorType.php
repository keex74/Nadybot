<?php declare(strict_types=1);

namespace Nadybot\Modules\ALIEN_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\{DBTable, Profession};

#[Table(name: 'ofabarmortype', shared: Shared::Yes)]
class OfabArmorType extends DBTable {
	public function __construct(
		public int $type,
		public Profession $profession,
	) {
	}
}
