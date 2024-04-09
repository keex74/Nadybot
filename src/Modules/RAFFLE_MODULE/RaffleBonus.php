<?php declare(strict_types=1);

namespace Nadybot\Modules\RAFFLE_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'raffle_bonus')]
class RaffleBonus extends DBTable {
	public function __construct(
		#[PK] public string $name,
		public int $bonus,
	) {
	}
}
