<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes\DB, DBRow};

#[DB\Table(name: 'preferences')]
class Preferences extends DBRow {
	public function __construct(
		#[DB\PK] public string $sender,
		#[DB\PK] public string $name,
		public string $value,
	) {
	}
}
