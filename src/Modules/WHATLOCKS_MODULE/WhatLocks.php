<?php declare(strict_types=1);

namespace Nadybot\Modules\WHATLOCKS_MODULE;

use Nadybot\Core\Attributes\DB;
use Nadybot\Core\DBRow;
use Nadybot\Modules\ITEMS_MODULE\AODBEntry;

#[DB\Table(name: 'what_locks', shared: DB\Shared::Yes)]
class WhatLocks extends DBRow {
	#[DB\PK] public int $item_id;
	#[DB\PK] public int $skill_id;
	public int $duration;

	#[DB\Ignore] public ?AODBEntry $item = null;
}
