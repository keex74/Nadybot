<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'Cluster', shared: Shared::Yes)]
class Cluster extends DBTable {
	public function __construct(
		#[PK] public int $ClusterID,
		public int $EffectTypeID,
		public string $LongName,
		public string $OfficialName,
		public int $NPReq,
		public ?int $SkillID=null,
	) {
	}
}
