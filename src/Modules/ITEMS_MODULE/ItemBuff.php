<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'item_buffs', shared: Shared::Yes)]
class ItemBuff extends DBRow {
	public function __construct(
		public int $item_id,
		public int $attribute_id,
		public int $amount,
	) {
	}
}
