<?php declare(strict_types=1);

namespace Nadybot\Modules\HELPBOT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'research', shared: Shared::Yes)]
class Research extends DBRow {
	public function __construct(
		#[PK] public int $level,
		public int $sk,
		public int $levelcap,
	) {
	}
}
