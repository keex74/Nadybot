<?php declare(strict_types=1);

namespace Nadybot\Modules\WHOMPAH_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\{DBTable, Faction};

#[Table(name: 'whompah_cities', shared: Shared::Yes)]
class WhompahCity extends DBTable {
	public function __construct(
		#[PK] public int $id,
		public string $city_name,
		public string $zone,
		public Faction $faction,
		public string $short_name,
	) {
	}
}
