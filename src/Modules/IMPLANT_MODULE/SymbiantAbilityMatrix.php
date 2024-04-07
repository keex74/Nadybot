<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'SymbiantAbilityMatrix', shared: Shared::Yes)]
class SymbiantAbilityMatrix extends DBRow {
	public function __construct(
		#[PK] public int $SymbiantID,
		#[PK] public int $AbilityID,
		public int $Amount,
	) {
	}
}
