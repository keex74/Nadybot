<?php declare(strict_types=1);

namespace Nadybot\Core\Events;

use Nadybot\Core\Highway;

class LowLevelEventFeedEvent extends Event {
	public const EVENT_MASK = 'event-feed(*)';

	public function __construct(
		public string $type,
		public Highway\Connection $connection,
		public Highway\In\InPackage $highwayPackage,
	) {
	}
}
