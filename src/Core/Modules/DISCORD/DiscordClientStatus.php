<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordClientStatus implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param ?string $desktop User's status set for an active desktop (Windows, Linux, Mac) application session
	 * @param ?string $mobile  User's status set for an active mobile (iOS, Android) application session
	 * @param ?string $web     User's status set for an active web (browser, bot user) application session
	 */
	public function __construct(
		public ?string $desktop=null,
		public ?string $mobile=null,
		public ?string $web=null,
	) {
	}
}
