<?php declare(strict_types=1);

namespace Nadybot\Modules\GUIDE_MODULE;

class AOUGuide {
	public function __construct(
		public int $id,
		public string $name,
		public string $description,
	) {
	}
}
