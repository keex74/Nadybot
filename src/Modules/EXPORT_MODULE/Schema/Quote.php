<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Quote {
	/**
	 * @param string     $quote       The quote
	 * @param ?Character $contributor The character contributing this quote
	 * @param ?Channel   $channel     Channel on which the quote was added
	 * @param ?int       $time        When was the quote contributed?
	 */
	public function __construct(
		public string $quote,
		public ?Character $contributor=null,
		public ?Channel $channel=null,
		public ?int $time=null,
	) {
	}
}
