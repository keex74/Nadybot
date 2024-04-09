<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'pocketboss', shared: Shared::Yes)]
class Pocketboss extends DBTable {
	public function __construct(
		#[PK] public int $id,
		public string $pb,
		public string $pb_location,
		public string $bp_mob,
		public int $bp_lvl,
		public string $bp_location,
		public string $type,
		public string $slot,
		public string $line,
		public int $ql,
		public int $itemid,
	) {
	}
}
