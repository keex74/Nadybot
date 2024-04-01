<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordEmbedProvider implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param ?string $name name of the provider
	 * @param ?string $url  URL of the provider
	 */
	public function __construct(
		public ?string $name=null,
		public ?string $url=null,
	) {
	}
}
