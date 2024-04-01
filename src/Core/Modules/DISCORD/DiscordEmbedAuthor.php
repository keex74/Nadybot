<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordEmbedAuthor implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param string  $name           name of the author
	 * @param ?string $url            url of author (only supports http(s))
	 * @param ?string $icon_url       url of author icon (only supports http(s) and attachments)
	 * @param ?string $proxy_icon_url a proxied url of author icon
	 */
	public function __construct(
		public string $name,
		public ?string $url=null,
		public ?string $icon_url=null,
		public ?string $proxy_icon_url=null,
	) {
	}
}
