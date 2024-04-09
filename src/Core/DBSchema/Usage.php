<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes\DB, DBTable};

#[DB\Table(name: 'usage')]
class Usage extends DBTable {
	public function __construct(
		public string $type,
		public string $command,
		public string $sender,
		public int $dt,
	) {
	}
}
