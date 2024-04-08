<?php declare(strict_types=1);

namespace Nadybot\Core;

class ClassInstance {
	public function __construct(
		public string $name,
		public string $className,
		public bool $overwrite=false,
	) {
	}
}
