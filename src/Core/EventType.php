<?php declare(strict_types=1);

namespace Nadybot\Core;

class EventType {
	/**
	 * @param string  $name        The name of the event
	 * @param ?string $description The optional description, explaining when it occurs
	 */
	public function __construct(
		public string $name,
		public ?string $description=null,
	) {
	}
}
