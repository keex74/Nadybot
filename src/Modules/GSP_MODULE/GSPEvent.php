<?php declare(strict_types=1);

namespace Nadybot\Modules\GSP_MODULE;

use Nadybot\Core\Event;

abstract class GSPEvent extends Event {
	public function __construct(
		public Show $show,
	) {
	}
}
