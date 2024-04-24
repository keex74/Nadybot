<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{AutoInc, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'item_groups', shared: Shared::Yes)]
class ItemGroup extends DBTable {
	public function __construct(
		#[AutoInc] public int $id,
		public int $group_id,
		public int $item_id,
	) {
	}
}