<?php declare(strict_types=1);

namespace Nadybot\Modules\GSP_MODULE;

use Nadybot\Core\Events\Event;

abstract class GSPEvent extends Event {
	public const EVENT_MASK = 'gsp(*)';

	public Show $show;
}
