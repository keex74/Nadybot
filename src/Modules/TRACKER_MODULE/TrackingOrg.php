<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE;

use DateTimeInterface;
use Nadybot\Core\{Attributes as NCA, DBRow};
use Nadybot\Modules\ORGLIST_MODULE\Organization;
use Safe\DateTimeImmutable;

class TrackingOrg extends DBRow {
	public DateTimeInterface $added_dt;

	public function __construct(
		public int $org_id,
		public string $added_by,
		?DateTimeInterface $added_dt=null,
		#[NCA\DB\Ignore] public ?Organization $org=null,
	) {
		$this->added_dt = $added_dt ?? new DateTimeImmutable();
	}
}
