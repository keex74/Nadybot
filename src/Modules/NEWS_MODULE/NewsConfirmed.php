<?php declare(strict_types=1);

namespace Nadybot\Modules\NEWS_MODULE;

use Nadybot\Core\{Attributes\DB, DBTable};

#[DB\Table(name: 'news_confirmed', shared: DB\Shared::Yes)]
class NewsConfirmed extends DBTable {
	/** @param int $id The confirmed news entry */
	public function __construct(
		#[DB\PK] public int $id,
		#[DB\PK] public string $player,
		public int $time,
	) {
	}
}
