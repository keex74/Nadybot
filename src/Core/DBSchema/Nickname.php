<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'migrations', shared: Shared::No)]
class Nickname extends DBRow {
	public function __construct(
		public string $main,
		public string $nick,
	) {
	}
}
