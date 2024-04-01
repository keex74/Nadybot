<?php declare(strict_types=1);

namespace Nadybot\Modules\HELPBOT_MODULE;

use Nadybot\Core\{Attributes as NCA, DBRow, Playfield};

class DynaDB extends DBRow {
	public function __construct(
		#[NCA\DB\ColName('playfield_id')] public Playfield $playfield,
		public string $mob,
		public int $min_ql,
		public int $max_ql,
		public int $x_coord,
		public int $y_coord,
	) {
	}
}
