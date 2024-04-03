<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'discord_mapping')]
class DiscordMapping extends DBRow {
	public function __construct(
		#[PK] public string $name,
		#[PK] public string $discord_id,
		public int $created,
		public ?string $token=null,
		public ?int $confirmed=null,
	) {
	}
}
