<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Auction {
	public function __construct(
		public string $item,
		#[Min(1)] public ?int $raidId=null,
		public ?Character $startedBy=null,
		public ?int $timeStart=null,
		public ?int $timeEnd=null,
		public ?Character $winner=null,
		public ?float $cost=null,
		public ?bool $reimbursed=null,
	) {
	}
}
