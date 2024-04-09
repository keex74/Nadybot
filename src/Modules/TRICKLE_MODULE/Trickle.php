<?php declare(strict_types=1);

namespace Nadybot\Modules\TRICKLE_MODULE;

use Nadybot\Core\Attributes\DB\{Ignore, PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'trickle', shared: Shared::Yes)]
class Trickle extends DBTable {
	public function __construct(
		#[PK] public readonly int $id,
		public readonly int $skill_id,
		public readonly string $groupName,
		public readonly string $name,
		public readonly float $amountAgi,
		public readonly float $amountInt,
		public readonly float $amountPsy,
		public readonly float $amountSta,
		public readonly float $amountStr,
		public readonly float $amountSen,
		#[Ignore] public ?float $amount=null,
	) {
	}
}
