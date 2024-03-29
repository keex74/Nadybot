<?php declare(strict_types=1);

namespace Nadybot\Modules\PVP_MODULE\Attributes;

use Attribute;
use EventSauce\ObjectHydrator\{ObjectMapper, PropertyCaster};
use Nadybot\Modules\PVP_MODULE\Timing;

#[Attribute(Attribute::TARGET_PARAMETER)]

class CastToTiming implements PropertyCaster {
	public function cast(mixed $value, ObjectMapper $hydrator): mixed {
		return match ($value) {
			'StaticEurope' => Timing::EU->value,
			'StaticUS' => Timing::US->value,
			default => Timing::Dynamic->value,
		};
	}
}
