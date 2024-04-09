<?php declare(strict_types=1);

namespace Nadybot\Modules\TIMERS_MODULE;

use stdClass;

class Alert extends stdClass {
	/**
	 * @param string $message The message to display for this alert
	 * @param int    $time    Timestamp when to display this alert
	 */
	public function __construct(
		public string $message,
		public int $time,
	) {
	}
}
