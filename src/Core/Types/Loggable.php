<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

interface Loggable {
	public function toLog(): string;
}
