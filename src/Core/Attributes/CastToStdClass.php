<?php

declare(strict_types=1);

namespace Nadybot\Core\Attributes;

use function Safe\{json_decode, json_encode};

use Attribute;
use EventSauce\ObjectHydrator\{ObjectMapper, PropertyCaster};
use stdClass;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class CastToStdClass implements PropertyCaster {
	public function cast(mixed $value, ObjectMapper $hydrator): stdClass {
		return json_decode(json_encode($value), false);
	}
}
