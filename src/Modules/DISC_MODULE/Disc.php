<?php declare(strict_types=1);

namespace Nadybot\Modules\DISC_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'discs', shared: Shared::Yes)]
class Disc extends DBRow {
	public function __construct(
		#[PK] public int $disc_id,
		public int $crystal_id,
		public int $crystal_ql,
		public int $disc_ql,
		public string $disc_name,
		public string $crystal_name,
		public ?string $comment=null,
	) {
	}
}
