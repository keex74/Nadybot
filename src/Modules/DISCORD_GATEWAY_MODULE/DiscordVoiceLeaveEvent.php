<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE;

use Nadybot\Core\Modules\DISCORD\{DiscordChannel, GuildMember};

class DiscordVoiceLeaveEvent extends DiscordVoiceEvent {
	public const EVENT_MASK = 'discord_voice_leave';

	public function __construct(
		DiscordChannel $discord_channel,
		GuildMember $member,
	) {
		parent::__construct(
			discord_channel: $discord_channel,
			member: $member,
			type: self::EVENT_MASK,
		);
	}
}
