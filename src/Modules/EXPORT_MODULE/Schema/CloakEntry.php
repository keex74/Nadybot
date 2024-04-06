<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class CloakEntry {
	/**
	 * @param bool       $cloakOn     Was the cloak raised (true) or lowered (false)?
	 * @param int        $time        When did the event happen?
	 * @param ?Character $character   The character raising or lowering the cloak
	 * @param ?bool      $manualEntry Was the cloak manually lower or raised via a bot command? Then true. If this entry came from parsing an org message, then false.
	 */
	public function __construct(
		public bool $cloakOn,
		public int $time,
		public ?Character $character=null,
		public ?bool $manualEntry=null,
	) {
	}
}
