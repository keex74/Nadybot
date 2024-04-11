<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

abstract class AuctionStatusEvent extends AuctionEvent {
	public const EVENT_MASK = 'auction(*)';

	/**
	 * @param Auction $auction The auction
	 * @param ?string $sender  If set, this is the person ending or cancelling an auction
	 */
	public function __construct(
		Auction $auction,
		public ?string $sender,
	) {
		parent::__construct(auction: $auction);
	}
}
