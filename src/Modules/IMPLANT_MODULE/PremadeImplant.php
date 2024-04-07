<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'PremadeImplant', shared: Shared::Yes)]
class PremadeImplant extends DBRow {
	public function __construct(
		#[PK] public int $ImplantTypeID,
		#[PK] public int $ProfessionID,
		public int $AbilityID,
		public int $ShinyClusterID,
		public int $BrightClusterID,
		public int $FadedClusterID,
	) {
	}
}
