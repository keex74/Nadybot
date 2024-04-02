<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordMessageReference implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param ?string $message_id         id of the originating message
	 * @param ?string $channel_id         id of the originating message's channel
	 * @param ?string $guild_id           id of the originating message's guild
	 * @param ?bool   $fail_if_not_exists when sending, whether to error if the referenced
	 *                                    message doesn't exist instead of sending as a
	 *                                    normal (non-reply) message, default true
	 */
	public function __construct(
		public ?string $message_id=null,
		public ?string $channel_id=null,
		public ?string $guild_id=null,
		public ?bool $fail_if_not_exists=true,
	) {
	}
}
