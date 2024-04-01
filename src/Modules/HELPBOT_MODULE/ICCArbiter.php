<?php declare(strict_types=1);

namespace Nadybot\Modules\HELPBOT_MODULE;

use DateTimeInterface;
use Nadybot\Core\{Attributes as NCA, DBRow};

class ICCArbiter extends DBRow {
	public function __construct(
		public string $type,
		public DateTimeInterface $start,
		public DateTimeInterface $end,
		#[NCA\DB\AutoInc] public ?int $id=null,
	) {
	}
}
