<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class SelectOptionValue implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param string  $label       the user-facing name of the option, max 100 characters
	 * @param string  $value       the dev-defined value of the option, max 100 characters
	 * @param ?string $description an additional description of the option, max 100 characters
	 * @param ?Emoji  $emoji       id, name, and animated
	 * @param ?bool   $default     will render this option as selected by default
	 */
	public function __construct(
		public string $label,
		public string $value,
		public ?string $description=null,
		public ?Emoji $emoji=null,
		public ?bool $default=null,
	) {
	}
}
