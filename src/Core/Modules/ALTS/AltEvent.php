<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\ALTS;

use Nadybot\Core\Events\Event;

abstract class AltEvent extends Event {
	public const EVENT_MASK = 'alt(*)';

	public string $main;
	public string $alt;
	public ?bool $validated;
}
