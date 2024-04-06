<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class AltChar {
	/**
	 * @param Character $alt             The alt character.
	 * @param ?bool     $validatedByAlt  Has the alt agreed to be added as an alt?
	 * @param ?bool     $validatedByMain Has the main confirmed that character as their alt?
	 * @param ?int      $time            When was the alt added?
	 */
	public function __construct(
		public Character $alt,
		public ?bool $validatedByAlt=null,
		public ?bool $validatedByMain=null,
		public ?int $time=null,
	) {
	}
}
