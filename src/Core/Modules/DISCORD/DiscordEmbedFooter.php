<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordEmbedFooter implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param string  $text           Footer text
	 * @param ?string $icon_url       url of footer icon (only supports http(s) and attachments)
	 * @param ?string $proxy_icon_url a proxied url of footer icon
	 */
	public function __construct(
		public string $text,
		public ?string $icon_url=null,
		public ?string $proxy_icon_url=null,
	) {
	}
}
