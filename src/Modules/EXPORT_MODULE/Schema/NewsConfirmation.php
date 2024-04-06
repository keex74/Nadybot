<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class NewsConfirmation {
	/**
	 * @param Character $character        The character who confirmed the news
	 * @param ?int      $confirmationTime Timestamp of when the news were confirmed by that character
	 */
	public function __construct(
		public Character $character,
		public ?int $confirmationTime=null,
	) {
	}
}
