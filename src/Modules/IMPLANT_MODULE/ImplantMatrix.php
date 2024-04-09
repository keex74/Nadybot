<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'ImplantMatrix', shared: Shared::Yes)]
class ImplantMatrix extends DBTable {
	public function __construct(
		#[PK] public int $ID,
		public int $ShiningID,
		public int $BrightID,
		public int $FadedID,
		public int $AbilityID,
		public int $TreatQL1,
		public int $AbilityQL1,
		public int $TreatQL200,
		public int $AbilityQL200,
		public int $TreatQL201,
		public int $AbilityQL201,
		public int $TreatQL300,
		public int $AbilityQL300,
	) {
	}
}
