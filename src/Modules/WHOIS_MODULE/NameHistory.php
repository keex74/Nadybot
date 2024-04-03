<?php declare(strict_types=1);

namespace Nadybot\Modules\WHOIS_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'name_history', shared: Shared::Yes)]
class NameHistory extends DBRow {
	public function __construct(
		public int $charid,
		public string $name,
		public int $dimension,
		public int $dt,
	) {
	}
}
