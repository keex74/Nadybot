<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Ban {
	/**
	 * @param Character  $character The banned character
	 * @param ?Character $bannedBy  Person who issued the ban
	 * @param ?string    $banReason Reason for the ban
	 * @param ?int       $banStart  When was the ban issued?
	 * @param ?int       $banEnd    If set, this is only a temporary ban and this is the timestamp when it ends.
	 */
	public function __construct(
		public Character $character,
		public ?Character $bannedBy=null,
		public ?string $banReason=null,
		public ?int $banStart=null,
		public ?int $banEnd=null,
	) {
	}
}
