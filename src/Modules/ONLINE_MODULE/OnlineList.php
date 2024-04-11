<?php declare(strict_types=1);

namespace Nadybot\Modules\ONLINE_MODULE;

class OnlineList {
	/** @param string[] $mains */
	public function __construct(
		public int $count,
		public int $countMains,
		public string $blob,
		public array $mains,
	) {
	}
}
