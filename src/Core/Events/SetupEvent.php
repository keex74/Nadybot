<?php declare(strict_types=1);

namespace Nadybot\Core\Events;

class SetupEvent extends Event {
	public const EVENT_MASK = 'setup';

	public function __construct() {
		$this->type = self::EVENT_MASK;
	}
}