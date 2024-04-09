<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'rateignorelist', shared: Shared::Yes)]
class RateIgnoreList extends DBTable {
	public function __construct(
		#[PK] public string $name,
		public string $added_by,
		public int $added_dt,
	) {
	}
}
