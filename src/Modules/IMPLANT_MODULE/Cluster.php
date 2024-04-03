<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'Cluster', shared: Shared::Yes)]
class Cluster extends DBRow {
	public function __construct(
		public int $ClusterID,
		public int $EffectTypeID,
		public string $LongName,
		public string $OfficialName,
		public int $NPReq,
		public int $SkillID,
	) {
	}
}
