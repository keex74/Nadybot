<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'SymbiantAbilityMatrix', shared: Shared::Yes)]
class SymbiantAbilityMatrix extends DBTable {
	public function __construct(
		public int $SymbiantID,
		public int $AbilityID,
		public int $Amount,
	) {
	}
}
