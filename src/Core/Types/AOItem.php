<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

interface AOItem extends AOItemSpec {
	public function getQL(): int;

	public function setQL(int $ql): self;
}
