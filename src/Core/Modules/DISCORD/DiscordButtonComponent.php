<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordButtonComponent implements Stringable {
	use ReducedStringableTrait;

	public const STYLE_PRIMARY = 1;
	public const STYLE_SECONDARY = 2;
	public const STYLE_SUCCESS = 3;
	public const STYLE_DANGER = 4;
	public const STYLE_LINK = 5;

	/**
	 * @param int     $style     one of button styles
	 * @param string  $label     text that appears on the button, max 80 characters
	 * @param ?object $emoji     name, id, and animated
	 * @param ?string $custom_id a developer-defined identifier for the button, max 100 characters
	 * @param ?string $url       a url for link-style buttons
	 * @param bool    $disabled  whether the button is disabled (default false)
	 */
	public function __construct(
		public int $style,
		public string $label,
		public int $type=2,
		public ?object $emoji=null,
		public ?string $custom_id=null,
		public ?string $url=null,
		public bool $disabled=false,
	) {
	}
}
