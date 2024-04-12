<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

interface AOIcon {
	public function getIconID(): int;

	public function getIcon(): string;
}
