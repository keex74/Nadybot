<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Ban {
	public function __construct(
		public Character $character,
		public ?Character $bannedBy=null,
		public ?string $banReason=null,
		public ?int $banStart=null,
		public ?int $banEnd=null,
	) {
	}
}
