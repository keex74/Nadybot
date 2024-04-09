<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'ImplantType', shared: Shared::Yes)]
class ImplantType extends DBTable {
	public function __construct(
		#[PK] public int $ImplantTypeID,
		public string $Name,
		public string $ShortName,
	) {
	}
}
