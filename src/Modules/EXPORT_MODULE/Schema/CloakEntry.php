<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class CloakEntry {
	public function __construct(
		public bool $cloakOn,
		public ?Character $character=null,
		public ?bool $manualEntry=null,
		public ?int $time=null,
	) {
	}
}
