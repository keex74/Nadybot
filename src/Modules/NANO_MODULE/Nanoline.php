<?php declare(strict_types=1);

namespace Nadybot\Modules\NANO_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'nano_lines', shared: Shared::Yes)]
class Nanoline extends DBRow {
	public function __construct(
		#[PK] public int $strain_id,
		public string $name,
	) {
	}
}
