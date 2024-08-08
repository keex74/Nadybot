<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'EffectValue', shared: Shared::Yes)]
class EffectValue extends DBTable {
	public function __construct(
		#[PK] public int $EffectID,
		public string $Name,
		public int $Q200Value,
	) {
	}
}
