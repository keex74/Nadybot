<?php declare(strict_types=1);

namespace Nadybot\Core\Config;

use EventSauce\ObjectHydrator\PropertyCasters\CastToType;

class AutoUnfreeze {
	public function __construct(
		#[CastToType('bool')] public bool $enabled=false,
		public bool $useNadyproxy=true,
	) {
	}
}
