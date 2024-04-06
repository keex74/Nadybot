<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class RaidPointEntry {
	/**
	 * @param Character $character  The character with raid points
	 * @param float     $raidPoints How many raid points does the character have?
	 */
	public function __construct(
		public Character $character,
		public float $raidPoints,
	) {
	}
}
