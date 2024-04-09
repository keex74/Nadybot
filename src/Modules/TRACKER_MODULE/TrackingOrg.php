<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE;

use DateTimeInterface;
use Nadybot\Core\{Attributes\DB, DBTable};
use Nadybot\Modules\ORGLIST_MODULE\Organization;
use Safe\DateTimeImmutable;

#[DB\Table(name: 'tracking_org')]
class TrackingOrg extends DBTable {
	public DateTimeInterface $added_dt;

	public function __construct(
		#[DB\PK] public int $org_id,
		public string $added_by,
		?DateTimeInterface $added_dt=null,
		#[DB\Ignore] public ?Organization $org=null,
	) {
		$this->added_dt = $added_dt ?? new DateTimeImmutable();
	}
}
