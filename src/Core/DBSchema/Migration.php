<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes as NCA, DBRow};
use Safe\DateTimeImmutable;

class Migration extends DBRow {
	public function __construct(
		public string $module,
		public string $migration,
		public DateTimeImmutable $applied_at,
		#[NCA\DB\AutoInc] public ?int $id=null,
	) {
	}
}
