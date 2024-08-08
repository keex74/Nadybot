<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'ClusterType', shared: Shared::Yes)]
class ClusterType extends DBTable {
	public function __construct(
		#[PK] public int $ClusterTypeID,
		public string $Name,
	) {
	}
}
