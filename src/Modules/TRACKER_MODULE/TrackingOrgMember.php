<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'tracking_org_member')]
class TrackingOrgMember extends DBRow {
	public function __construct(
		#[PK] public int $org_id,
		#[PK] public int $uid,
		public string $name,
		public bool $hidden=false,
	) {
	}
}
