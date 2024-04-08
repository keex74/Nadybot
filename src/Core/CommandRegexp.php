<?php declare(strict_types=1);

namespace Nadybot\Core;

class CommandRegexp {
	public function __construct(
		public string $match,
		public ?string $variadicMatch=null
	) {
	}
}
