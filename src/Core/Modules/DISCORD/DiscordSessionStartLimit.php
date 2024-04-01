<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordSessionStartLimit implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param int $total           The total number of session starts the current user is allowed
	 * @param int $remaining       The remaining number of session starts the current user is allowed
	 * @param int $reset_after     The number of milliseconds after which the limit resets
	 * @param int $max_concurrency The number of identify requests allowed per 5 seconds
	 */
	public function __construct(
		public int $total,
		public int $remaining,
		public int $reset_after,
		public int $max_concurrency,
	) {
	}
}
