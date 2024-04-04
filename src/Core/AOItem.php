<?php declare(strict_types=1);

namespace Nadybot\Core;

interface AOItem extends AOItemSpec {
	public function getQL(): int;

	public function setQL(int $ql): self;
}
