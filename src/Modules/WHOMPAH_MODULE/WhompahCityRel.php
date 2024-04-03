<?php declare(strict_types=1);

namespace Nadybot\Modules\WHOMPAH_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'whompah_cities_rel', shared: Shared::Yes)]
class WhompahCityRel extends DBRow {
	public function __construct(
		public int $city1_id,
		public int $city2_id,
	) {
	}
}
