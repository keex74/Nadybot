<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class VoiceState implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param ?string      $channel_id  If unset, then the user disconnected from the voice channel
	 * @param ?GuildMember $member      the guild member this voice state is for
	 * @param string       $session_id  the session id for this voice state
	 * @param bool         $deaf        whether this user is deafened by the server
	 * @param bool         $mute        whether this user is muted by the server
	 * @param bool         $self_deaf   whether this user is locally deafened
	 * @param bool         $self_mute   whether this user is locally muted
	 * @param ?bool        $self_stream whether this user is streaming using "Go Live"
	 * @param bool         $self_video  whether this user's camera is enabled
	 * @param bool         $suppress    whether this user is muted by the current user
	 */
	public function __construct(
		public ?string $guild_id,
		public ?string $channel_id,
		public ?string $user_id,
		public ?GuildMember $member,
		public string $session_id,
		public bool $deaf,
		public bool $mute,
		public bool $self_deaf,
		public bool $self_mute,
		public ?bool $self_stream,
		public bool $self_video,
		public bool $suppress,
	) {
	}
}
