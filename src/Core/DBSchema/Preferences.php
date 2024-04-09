<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes\DB, DBTable};

#[DB\Table(name: 'preferences')]
class Preferences extends DBTable {
	public function __construct(
		#[DB\PK] public string $sender,
		#[DB\PK] public string $name,
		public string $value,
	) {
	}
}
