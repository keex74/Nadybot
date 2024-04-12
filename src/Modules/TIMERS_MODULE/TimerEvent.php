<?php declare(strict_types=1);

namespace Nadybot\Modules\TIMERS_MODULE;

use Nadybot\Core\Events\Event;

abstract class TimerEvent extends Event {
	public const EVENT_MASK = 'timer(*)';

	public function __construct(
		public Timer $timer,
	) {
	}
}
