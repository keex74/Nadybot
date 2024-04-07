<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'item_types', shared: Shared::Yes)]
class ItemType extends DBRow {
	public function __construct(
		#[PK] public int $item_id,
		public string $item_type,
	) {
	}
}
