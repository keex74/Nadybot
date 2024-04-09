<?php declare(strict_types=1);

namespace Nadybot\Modules\WHOIS_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'name_history', shared: Shared::Yes)]
class NameHistory extends DBTable {
	public function __construct(
		#[PK] public int $charid,
		#[PK] public string $name,
		#[PK] public int $dimension,
		public int $dt,
	) {
	}
}
