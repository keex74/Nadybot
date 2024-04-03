<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'skills', shared: Shared::Yes)]
class Skill extends DBRow {
	public function __construct(
		#[PK] public int $id,
		public string $name,
		public string $unit,
	) {
	}
}
