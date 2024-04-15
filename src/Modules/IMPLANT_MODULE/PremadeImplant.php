<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'premade_implant', shared: Shared::Yes)]
class PremadeImplant extends DBTable {
	public function __construct(
		public int $ImplantTypeID,
		public int $ProfessionID,
		public int $AbilityID,
		public int $ShinyClusterID,
		public int $BrightClusterID,
		public int $FadedClusterID,
	) {
	}
}
