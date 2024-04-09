<?php declare(strict_types=1);

namespace Nadybot\Modules\HELPBOT_MODULE;

class Timezone {
	public function __construct(
		public string $name,
		public float $offset,
		public string $time,
	) {
	}
}
