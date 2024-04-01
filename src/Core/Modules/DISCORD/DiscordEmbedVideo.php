<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordEmbedVideo implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param ?string $url       source url of video
	 * @param ?string $proxy_url a proxied url of the video
	 * @param ?int    $height    height of the video
	 * @param ?int    $width     height of the video
	 */
	public function __construct(
		public ?string $url=null,
		public ?string $proxy_url=null,
		public ?int $height=null,
		public ?int $width=null,
	) {
	}
}
