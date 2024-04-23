<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE;

class RelayMessage {
	/** @param list<string> $packages */
	public function __construct(
		public ?string $sender=null,
		public array $packages=[],
	) {
	}
}
