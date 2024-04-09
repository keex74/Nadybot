<?php declare(strict_types=1);

namespace Nadybot\Modules\HELPBOT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'research', shared: Shared::Yes)]
class Research extends DBTable {
	public function __construct(
		public int $level,
		public int $sk,
		public int $levelcap,
	) {
	}
}
