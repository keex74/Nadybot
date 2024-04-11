<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

class AuctionBidEvent extends AuctionEvent {
	public const EVENT_MASK = 'auction(bid)';

	public function __construct(
		Auction $auction,
	) {
		parent::__construct(auction: $auction);
		$this->type = self::EVENT_MASK;
	}
}
