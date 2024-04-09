<?php declare(strict_types=1);

namespace Nadybot\Modules\CITY_MODULE;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBTable;

#[Table(name: 'org_city')]
class OrgCity extends DBTable {
	public function __construct(
		public int $time,
		public string $action,
		public string $player,
	) {
	}
}
