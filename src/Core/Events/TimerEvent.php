<?php declare(strict_types=1);

namespace Nadybot\Core\Events;

class TimerEvent extends Event {
	public const EVENT_MASK = 'timer(*)';

	public function __construct(int $time) {
		$this->type = "timer({$time})";
	}
}