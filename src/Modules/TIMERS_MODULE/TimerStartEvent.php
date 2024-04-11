<?php declare(strict_types=1);

namespace Nadybot\Modules\TIMERS_MODULE;

class TimerStartEvent extends TimerEvent {
	public const EVENT_MASK = 'timer(start)';

	public function __construct(
		Timer $timer,
	) {
		parent::__construct(timer: $timer);
		$this->type = self::EVENT_MASK;
	}
}
