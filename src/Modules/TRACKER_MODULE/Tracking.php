<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBTable;

#[Table(name: 'tracking')]
class Tracking extends DBTable {
	public function __construct(
		public int $uid,
		public int $dt,
		public string $event,
	) {
	}
}
