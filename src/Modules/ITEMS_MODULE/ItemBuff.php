<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'item_buffs', shared: Shared::Yes)]
class ItemBuff extends DBRow {
	public function __construct(
		#[PK] public int $item_id,
		#[PK] public int $attribute_id,
		public int $amount,
	) {
	}
}
