<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\ExportCharacter;

class ExportRaider {
	/**
	 * @param ExportCharacter $character The raider
	 * @param ?int            $joinTime  When did the character join the raid?
	 * @param ?int            $leaveTime When did the character leave the raid?
	 */
	public function __construct(
		public ExportCharacter $character,
		public ?int $joinTime=null,
		public ?int $leaveTime=null,
	) {
	}
}
