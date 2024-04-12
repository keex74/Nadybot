<?php declare(strict_types=1);

namespace Nadybot\Core\Events;

use Nadybot\Core\StringableTrait;
use Nadybot\Core\Types\DoNotSerializePublicFunctions;
use Stringable;

abstract class Event implements Stringable, DoNotSerializePublicFunctions {
	use StringableTrait;

	/** @var string */
	public const EVENT_MASK = '*';

	public string $type;
}
