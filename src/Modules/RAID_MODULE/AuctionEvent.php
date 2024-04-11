<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\Event;

abstract class AuctionEvent extends Event {
	public const EVENT_MASK = 'auction(*)';

	/** @param Auction $auction The auction */
	public function __construct(
		public Auction $auction,
	) {
	}
}
