<?php declare(strict_types=1);

namespace Nadybot\Core\Attributes;

use Attribute;
use EventSauce\ObjectHydrator\{ObjectMapper, PropertySerializer};

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
final class Confidential implements PropertySerializer {
	public static bool $active = false;

	public function serialize(mixed $value, ObjectMapper $hydrator): mixed {
		if (static::$active === false) {
			return $value;
		}
		return isset($value) ? '******' : null;
	}
}
