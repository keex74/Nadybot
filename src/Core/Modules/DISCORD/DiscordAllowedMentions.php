<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Stringable;

class DiscordAllowedMentions implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param DiscordAllowedMentionType[] $parse        An array of allowed mention types to parse from the content.
	 * @param string[]                    $roles        Array of role_ids to mention (Max size of 100)
	 * @param string[]                    $users        Array of user_ids to mention (Max size of 100)
	 * @param bool                        $replied_user For replies, whether to mention the author of the message being replied to (default false)
	 *
	 * @psalm-param list<DiscordAllowedMentionType> $parse
	 * @psalm-param list<string>                    $roles
	 * @psalm-param list<string>                    $users
	 */
	public function __construct(
		#[CastListToType(DiscordAllowedMentionType::class)] public array $parse=[],
		#[CastListToType('string')] public array $roles=[],
		#[CastListToType('string')] public array $users=[],
		public bool $replied_user=false,
	) {
	}
}
