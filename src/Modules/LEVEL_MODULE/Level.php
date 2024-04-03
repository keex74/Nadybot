<?php declare(strict_types=1);

namespace Nadybot\Modules\LEVEL_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'levels', shared: Shared::Yes)]
class Level extends DBRow {
	public function __construct(
		#[PK] public int $level,
		public int $mob_min,
		public int $mob_max,
		public int $teamMin,
		public int $teamMax,
		public int $pvpMin,
		public int $pvpMax,
		public int $xpsk,
		public int $tokens,
		public int $daily_mission_xp,
		public string $missions,
		public int $max_ai_level,
		public int $max_le_level,
	) {
	}
}
