<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class TrackerEvent {
	/**
	 * @param int    $time  Time when this event happened
	 * @param string $event What exactly happened on this event?
	 */
	public function __construct(
		public int $time,
		public string $event,
	) {
	}
}
