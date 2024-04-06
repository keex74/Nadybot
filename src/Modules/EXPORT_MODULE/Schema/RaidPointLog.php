<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class RaidPointLog {
	/**
	 * @param Character  $character         The character who got or lost raid points
	 * @param float      $raidPoints        How many raid points given or taken
	 * @param ?int       $time              Time when the change occurred
	 * @param ?Character $givenBy           Who gave the raid points?
	 * @param ?string    $reason            Why were the raid points given?
	 * @param ?bool      $givenByTick       True if the raidpoints were automatically given for raid participation
	 * @param ?bool      $givenIndividually True if these points were given to only this character, false if to the whole raidforce
	 * @param ?int       $raidId            If these points were given during a raid, this is the raid's ID
	 */
	public function __construct(
		public Character $character,
		public float $raidPoints,
		public ?int $time=null,
		public ?Character $givenBy=null,
		public ?string $reason=null,
		public ?bool $givenByTick=null,
		public ?bool $givenIndividually=null,
		public ?int $raidId=null,
	) {
	}
}
