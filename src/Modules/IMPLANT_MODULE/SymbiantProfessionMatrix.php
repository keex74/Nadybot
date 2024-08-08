<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'SymbiantProfessionMatrix', shared: Shared::Yes)]
class SymbiantProfessionMatrix extends DBTable {
	public function __construct(
		public int $SymbiantID,
		public int $ProfessionID,
	) {
	}
}
