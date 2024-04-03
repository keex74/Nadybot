<?php declare(strict_types=1);

namespace Nadybot\Modules\RECIPE_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'arulsaba', shared: Shared::Yes)]
class ArulSaba extends DBRow {
	public function __construct(
		#[PK] public string $name,
		public string $lesser_prefix,
		public string $regular_prefix,
		public string $buffs,
	) {
	}
}
