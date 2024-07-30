<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\BAN;

use Nadybot\Core\ExportCharacter;

class ExportedBan {
	/**
	 * @param ExportCharacter  $character The banned character
	 * @param ?ExportCharacter $bannedBy  Person who issued the ban
	 * @param ?string          $banReason Reason for the ban
	 * @param ?int             $banStart  When was the ban issued?
	 * @param ?int             $banEnd    If set, this is only a temporary ban and this is the timestamp when it ends.
	 */
	public function __construct(
		public ExportCharacter $character,
		public ?ExportCharacter $bannedBy=null,
		public ?string $banReason=null,
		public ?int $banStart=null,
		public ?int $banEnd=null,
	) {
	}
}
