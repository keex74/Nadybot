<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordEmbedImage implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param ?string $url       source url of image (only supports http(s) and attachments)
	 * @param ?string $proxy_url a proxied url of the image
	 * @param ?int    $height    height of the image
	 * @param ?int    $width     height of the image
	 */
	public function __construct(
		public ?string $url=null,
		public ?string $proxy_url=null,
		public ?int $height=null,
		public ?int $width=null,
	) {
	}
}
