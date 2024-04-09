<?php

declare(strict_types=1);

namespace Nadybot\Modules\LOOT_MODULE;

use Nadybot\Core\Attributes\DB;
use Nadybot\Core\DBTable;
use Nadybot\Modules\ITEMS_MODULE\AODBEntry;

#[DB\Table(name: 'raid_loot', shared: DB\Shared::Yes)]
class RaidLoot extends DBTable {
	public function __construct(
		#[DB\PK] public int $id,
		public string $raid,
		public string $category,
		public int $ql,
		public string $name,
		public string $comment,
		public int $multiloot,
		public ?int $aoid=null,
		#[DB\Ignore] public ?AODBEntry $item=null,
	) {
	}
}
