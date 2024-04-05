<?php

declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use Attribute;
use EventSauce\ObjectHydrator\{ObjectMapper, PropertyCaster, PropertySerializer};
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class Min implements PropertyCaster, PropertySerializer {
	public function __construct(
		private int $min,
	) {
	}

	public function cast(mixed $value, ObjectMapper $hydrator): mixed {
		if (isset($value) && $value < $this->min) {
			throw new InvalidArgumentException("The minimum value is {$this->min}");
		}
		return $value;
	}

	public function serialize(mixed $value, ObjectMapper $hydrator): mixed {
		if (isset($value) && $value < $this->min) {
			throw new InvalidArgumentException("The minimum value is {$this->min}");
		}

		return $value;
	}
}
