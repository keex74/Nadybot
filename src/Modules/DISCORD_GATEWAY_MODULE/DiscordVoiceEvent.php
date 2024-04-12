<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE;

use Nadybot\Core\Events\Event;
use Nadybot\Core\Modules\DISCORD\{DiscordChannel, GuildMember};

abstract class DiscordVoiceEvent extends Event {
	public const EVENT_MASK = '*';

	public function __construct(
		public DiscordChannel $discord_channel,
		public GuildMember $member,
		string $type,
	) {
		$this->type = $type;
	}
}
