<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\Attributes\DB\{PK, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'admin')]
class Admin extends DBTable {
	public function __construct(
		#[PK] public string $name,
		public ?int $adminlevel=0,
	) {
	}
}
