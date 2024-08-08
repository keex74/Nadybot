<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'item_group_names', shared: Shared::Yes)]
class ItemGroupName extends DBTable {
	public function __construct(
		public int $group_id,
		public string $iname,
	) {
	}
}
