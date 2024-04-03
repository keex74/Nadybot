<?php declare(strict_types=1);

namespace Nadybot\Modules\NEWS_MODULE;

use Nadybot\Core\{Attributes as NCA, DBRow};

#[NCA\DB\Table(name: 'news_confirmed', shared: NCA\DB\Shared::Yes)]
class NewsConfirmed extends DBRow {
	/** @param ?int $id The internal ID of this news entry */
	public function __construct(
		#[NCA\DB\AutoInc] public ?int $id,
		public string $player,
		public int $time,
	) {
	}
}
