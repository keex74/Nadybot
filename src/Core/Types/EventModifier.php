<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

use Nadybot\Core\Routing\RoutableEvent;

interface EventModifier {
	public function modify(?RoutableEvent $event=null): ?RoutableEvent;
}
