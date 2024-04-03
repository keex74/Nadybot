<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'EffectTypeMatrix', shared: Shared::Yes)]
class EffectTypeMatrix extends DBRow {
	public function __construct(
		#[PK] public int $ID,
		public string $Name,
		public int $MinValLow,
		public int $MaxValLow,
		public int $MinValHigh,
		public int $MaxValHigh,
	) {
	}
}
