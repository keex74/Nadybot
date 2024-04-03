<?php declare(strict_types=1);

namespace Nadybot\Modules\HELPBOT_MODULE;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBRow;

#[Table(name: 'research')]
class Research extends DBRow {
	public function __construct(
		public int $level,
		public int $sk,
		public int $levelcap,
	) {
	}
}
