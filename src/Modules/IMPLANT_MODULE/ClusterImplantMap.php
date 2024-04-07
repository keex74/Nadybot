<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'ClusterImplantMap', shared: Shared::Yes)]
class ClusterImplantMap extends DBRow {
	public function __construct(
		public int $ImplantTypeID,
		public int $ClusterID,
		public int $ClusterTypeID,
	) {
	}
}
