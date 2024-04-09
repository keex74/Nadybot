<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'SymbiantClusterMatrix', shared: Shared::Yes)]
class SymbiantClusterMatrix extends DBTable {
	public function __construct(
		public int $SymbiantID,
		public int $ClusterID,
		public int $Amount,
	) {
	}
}
