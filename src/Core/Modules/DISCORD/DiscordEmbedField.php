<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordEmbedField implements Stringable {
	use ReducedStringableTrait;

	public function __construct(
		public string $name,
		public string $value,
		public ?bool $inline=null,
	) {
	}
}
