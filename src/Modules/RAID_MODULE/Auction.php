<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Modules\RAFFLE_MODULE\RaffleItem;

class Auction {
	/**
	 * @param RaffleItem $item       The item currently being auctioned
	 * @param string     $auctioneer The person auctioning the item
	 * @param int        $bid        The current bid
	 * @param int        $max_bid    The current maximum bid
	 * @param ?string    $top_bidder The current top bidder
	 * @param int        $end        UNIX timestamp when the auction ends
	 */
	public function __construct(
		public RaffleItem $item,
		public string $auctioneer,
		public int $bid=0,
		public int $max_bid=0,
		public ?string $top_bidder=null,
		public int $end=0,
	) {
	}
}
