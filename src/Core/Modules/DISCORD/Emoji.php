<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class Emoji implements Stringable {
	use ReducedStringableTrait;

	/** @param ?mixed[] $roles */
	public function __construct(
		public string $id,
		public string $name,
		public ?array $roles,
		public ?DiscordUser $user,
		public ?bool $require_colors,
		public ?bool $managed,
		public ?bool $animated,
		public ?bool $available,
	) {
	}
}
