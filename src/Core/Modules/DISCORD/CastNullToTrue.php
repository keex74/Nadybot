<?php

declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Attribute;
use EventSauce\ObjectHydrator\{ObjectMapper, PropertyCaster};

#[Attribute(Attribute::TARGET_PARAMETER)]
final class CastNullToTrue implements PropertyCaster {
	public function cast(mixed $value, ObjectMapper $hydrator): bool {
		return is_null($value) ? true : false;
	}
}
