<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE;

use Nadybot\Core\{Attributes as NCA, DBRow};

#[NCA\DB\Table(name: 'discord_slash_command')]
class DiscordSlashCommand extends DBRow {
	public function __construct(
		public string $cmd,
		#[NCA\DB\AutoInc] public ?int $id=null,
	) {
	}
}
