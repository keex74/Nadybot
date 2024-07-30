<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\ExportCharacter;

class ExportAuction {
	/**
	 * @param string           $item       The name of the item that was auctioned.
	 * @param ?int             $raidId     The number of the raid during which the item was auctioned.
	 * @param ?ExportCharacter $startedBy  The person auctioning the item.
	 * @param ?int             $timeStart  When did the auction start?
	 * @param ?int             $timeEnd    When did the auction end?
	 * @param ?ExportCharacter $winner     The winner of the item.
	 * @param ?float           $cost       How much did the item go for?
	 * @param ?bool            $reimbursed Did the winner get their points back for accidentally winning the auction?
	 */
	public function __construct(
		public string $item,
		public ?int $raidId=null,
		public ?ExportCharacter $startedBy=null,
		public ?int $timeStart=null,
		public ?int $timeEnd=null,
		public ?ExportCharacter $winner=null,
		public ?float $cost=null,
		public ?bool $reimbursed=null,
	) {
	}
}
