<?php declare(strict_types=1);

namespace Nadybot\Modules\ONLINE_MODULE;

use Nadybot\Core\Events\Event;

class OfflineEvent extends Event {
	public const EVENT_MASK = 'offline(*)';

	public function __construct(
		public string $player,
		public string $channel,
	) {
		$this->type = "offline({$channel})";
	}
}
