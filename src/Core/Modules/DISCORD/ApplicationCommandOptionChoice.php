<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class ApplicationCommandOptionChoice implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param string                $name               1-100 character choice name
	 * @param ?array<string,string> $name_localizations Localization dictionary for the name field. Values follow the same restrictions as name
	 * @param string|int|float      $value              Value for the choice, up to 100 characters if string
	 */
	public function __construct(
		public string $name,
		public ?array $name_localizations,
		public string|int|float $value,
	) {
	}
}
