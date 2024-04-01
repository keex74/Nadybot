<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordGateway implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param string                   $url                 The WSS URL that can be used for
	 *                                                      connecting to the gateway
	 * @param int                      $shards              The recommended number of shards
	 *                                                      to use when connecting
	 * @param DiscordSessionStartLimit $session_start_limit Information on the current session
	 *                                                      start limit
	 */
	public function __construct(
		public string $url,
		public int $shards,
		public DiscordSessionStartLimit $session_start_limit,
	) {
	}
}
