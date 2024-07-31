<?php declare(strict_types=1);

namespace Nadybot\Modules\QUOTE_MODULE;

use Nadybot\Core\{ExportChannel, ExportCharacter};

class ExportQuote {
	/**
	 * @param string           $quote       The quote
	 * @param ?ExportCharacter $contributor The character contributing this quote
	 * @param ?ExportChannel   $channel     Channel on which the quote was added
	 * @param ?int             $time        When was the quote contributed?
	 */
	public function __construct(
		public string $quote,
		public ?ExportCharacter $contributor=null,
		public ?ExportChannel $channel=null,
		public ?int $time=null,
	) {
	}
}
