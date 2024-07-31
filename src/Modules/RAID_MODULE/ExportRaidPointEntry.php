<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\ExportCharacter;

class ExportRaidPointEntry {
	/**
	 * @param ExportCharacter $character  The character with raid points
	 * @param float           $raidPoints How many raid points does the character have?
	 */
	public function __construct(
		public ExportCharacter $character,
		public float $raidPoints,
	) {
	}
}
