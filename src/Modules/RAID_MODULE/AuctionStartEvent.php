<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

class AuctionStartEvent extends AuctionStatusEvent {
	public const EVENT_MASK = 'auction(start)';

	public function __construct(
		Auction $auction,
		?string $sender=null,
	) {
		parent::__construct(auction: $auction, sender: $sender);
		$this->type = self::EVENT_MASK;
	}
}
