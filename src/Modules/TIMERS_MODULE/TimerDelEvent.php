<?php declare(strict_types=1);

namespace Nadybot\Modules\TIMERS_MODULE;

class TimerDelEvent extends TimerEvent {
	public const EVENT_MASK = 'timer(del)';

	public function __construct(
		Timer $timer,
	) {
		parent::__construct(timer: $timer);
		$this->type = self::EVENT_MASK;
	}
}
