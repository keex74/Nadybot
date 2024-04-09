<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE;

use Nadybot\Core\{Attributes as NCA, DBTable};

#[NCA\DB\Table(name: 'discord_invite')]
class DBDiscordInvite extends DBTable {
	public function __construct(
		public string $character,
		public string $token,
		public ?int $expires=null,
		#[NCA\DB\AutoInc] public ?int $id=null,
	) {
	}
}
