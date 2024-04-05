<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class AltChar {
	public function __construct(
		public Character $alt,
		public ?bool $validatedByAlt=null,
		public ?bool $validatedByMain=null,
		public ?int $time=null,
	) {
	}
}
