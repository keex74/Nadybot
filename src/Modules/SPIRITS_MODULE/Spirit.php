<?php declare(strict_types=1);

namespace Nadybot\Modules\SPIRITS_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'spiritsdb', shared: Shared::Yes)]
class Spirit extends DBTable {
	public function __construct(
		#[PK] public int $id,
		public string $name,
		public int $ql,
		public string $spot,
		public int $level,
		public int $agility,
		public int $sense,
	) {
	}
}
