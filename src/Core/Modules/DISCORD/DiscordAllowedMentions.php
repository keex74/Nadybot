<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordAllowedMentions implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param DiscordAllowedMentionType[] $parse        An array of allowed mention types to parse from the content.
	 * @param string[]                    $roles        Array of role_ids to mention (Max size of 100)
	 * @param string[]                    $users        Array of user_ids to mention (Max size of 100)
	 * @param bool                        $replied_user For replies, whether to mention the author of the message being replied to (default false)
	 */
	public function __construct(
		public array $parse=[],
		public array $roles=[],
		public array $users=[],
		public bool $replied_user=false,
	) {
	}
}
