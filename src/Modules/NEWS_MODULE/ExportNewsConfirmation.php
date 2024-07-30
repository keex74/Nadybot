<?php declare(strict_types=1);

namespace Nadybot\Modules\NEWS_MODULE;

use Nadybot\Core\ExportCharacter;

class ExportNewsConfirmation {
	/**
	 * @param ExportCharacter $character        The character who confirmed the news
	 * @param ?int            $confirmationTime Timestamp of when the news were confirmed by that character
	 */
	public function __construct(
		public ExportCharacter $character,
		public ?int $confirmationTime=null,
	) {
	}
}
