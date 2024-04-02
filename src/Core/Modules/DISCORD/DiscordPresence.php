<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordPresence implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param DiscordUser          $user          User whose presence is being updated
	 * @param ?string              $guild_id      ID of the guild
	 * @param ?string              $status        Either "idle", "dnd", "online", or "offline"
	 * @param ?Activity[]          $activities    User's current activities
	 * @param ?DiscordClientStatus $client_status User's platform-dependent status
	 */
	public function __construct(
		public DiscordUser $user,
		public ?string $guild_id=null,
		public ?string $status=null,
		public ?array $activities=null,
		public ?DiscordClientStatus $client_status=null,
	) {
	}
}
