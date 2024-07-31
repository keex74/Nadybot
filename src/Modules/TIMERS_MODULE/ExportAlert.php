<?php declare(strict_types=1);

namespace Nadybot\Modules\TIMERS_MODULE;

class ExportAlert {
	/**
	 * @param int     $time    Time for this alert to occur
	 * @param ?string $message Message to show when this alert is due
	 */
	public function __construct(
		public int $time,
		public ?string $message=null,
	) {
	}
}
