<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordComponent implements Stringable {
	use ReducedStringableTrait;

	public function __construct(
		public int $type,
	) {
	}
}
