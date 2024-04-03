<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBRow;

#[Table(name: 'discord_mapping')]
class DiscordMapping extends DBRow {
	public function __construct(
		public string $name,
		public string $discord_id,
		public int $created,
		public ?string $token=null,
		public ?int $confirmed=null,
	) {
	}
}
