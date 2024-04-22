<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Stringable;

class Emoji implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param ?string[] $roles
	 *
	 * @psalm-param ?list<string> $roles
	 */
	public function __construct(
		public string $id,
		public string $name,
		#[CastListToType('string')] public ?array $roles,
		public ?DiscordUser $user,
		public ?bool $require_colors,
		public ?bool $managed,
		public ?bool $animated,
		public ?bool $available,
	) {
	}
}
