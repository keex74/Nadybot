<?php declare(strict_types=1);

namespace Nadybot\Modules\WEBSOCKET_MODULE;

class NadySubscribe {
	/** @param list<string> $events */
	public function __construct(
		public array $events=[],
	) {
	}
}
