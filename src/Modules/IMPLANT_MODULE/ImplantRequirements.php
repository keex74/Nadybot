<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

class ImplantRequirements {
	public function __construct(
		public int $treatment,
		public int $abilities,
		public int $titleLevel,
	) {
	}
}
