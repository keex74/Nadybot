<?php declare(strict_types=1);

namespace Nadybot\Modules\CITY_MODULE;

use Nadybot\Modules\TIMERS_MODULE\Alert;

class WaveAlert extends Alert {
	/**
	 * @param string $message The message to display for this alert
	 * @param int    $time    Timestamp when to display this alert
	 * @param int    $wave    Which city raid wave are we in?
	 */
	public function __construct(
		string $message,
		int $time,
		public int $wave=1,
	) {
		parent::__construct(message: $message, time: $time);
	}
}
