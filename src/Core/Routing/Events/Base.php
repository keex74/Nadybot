<?php declare(strict_types=1);

namespace Nadybot\Core\Routing\Events;

class Base {
	public function __construct(
		public string $type,
		public bool $renderPath=true,
		public ?string $message=null,
	) {
	}
}
