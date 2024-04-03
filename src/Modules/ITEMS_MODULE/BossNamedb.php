<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'boss_namedb', shared: Shared::Yes)]
class BossNamedb extends DBRow {
	/**
	 * @param int    $bossid   The internal ID of this database entry
	 * @param string $bossname Full name of this boss
	 */
	public function __construct(
		public int $bossid,
		public string $bossname,
	) {
	}
}
