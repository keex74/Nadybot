<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'nickname', shared: Shared::Yes)]
class Nickname extends DBTable {
	public function __construct(
		#[PK] public string $main,
		public string $nick,
	) {
	}
}
