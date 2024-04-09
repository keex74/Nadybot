<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'tracked_users')]
class TrackedUser extends DBTable {
	public function __construct(
		#[PK] public int $uid,
		public string $name,
		public string $added_by,
		public int $added_dt,
		public bool $hidden=false,
	) {
	}
}
