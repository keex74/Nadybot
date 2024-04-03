<?php declare(strict_types=1);

namespace Nadybot\Modules\ONLINE_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'online', shared: Shared::Yes)]
class Online extends DBRow {
	public function __construct(
		public string $name,
		public string $channel,
		public string $channel_type,
		public string $added_by,
		public int $dt,
		public ?string $afk='',
	) {
	}
}
