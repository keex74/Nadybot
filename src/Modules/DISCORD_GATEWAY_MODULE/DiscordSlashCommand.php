<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE;

use Nadybot\Core\{Attributes as NCA, DBTable};

#[NCA\DB\Table(name: 'discord_slash_command')]
class DiscordSlashCommand extends DBTable {
	public function __construct(
		public string $cmd,
		#[NCA\DB\AutoInc] public ?int $id=null,
	) {
	}
}
