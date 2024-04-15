<?php declare(strict_types=1);

namespace Nadybot\Modules\HELPBOT_MODULE;

use Nadybot\Core\{Attributes as NCA, DBTable, Types\Playfield};

#[NCA\DB\Table(name: 'dynadb', shared: NCA\DB\Shared::Yes)]
class DynaDB extends DBTable {
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
