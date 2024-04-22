<?php declare(strict_types=1);

namespace Nadybot\Modules\PVP_MODULE;

class TrackerConfig {
	/**
	 * @param list<TrackerArgument> $arguments
	 * @param list<string>          $events
	 */
	public function __construct(
		public array $arguments=[],
		public array $events=[],
	) {
	}
}
