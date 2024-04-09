<?php declare(strict_types=1);

namespace Nadybot\Modules\ONLINE_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'online', shared: Shared::Yes)]
class Online extends DBTable {
	public function __construct(
		#[PK] public string $name,
		public string $channel,
		#[PK] public string $channel_type,
		#[PK] public string $added_by,
		public int $dt,
		public ?string $afk='',
	) {
	}
}
