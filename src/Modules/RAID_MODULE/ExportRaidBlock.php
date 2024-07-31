<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

use Nadybot\Core\ExportCharacter;

class ExportRaidBlock {
	/**
	 * @param ExportCharacter     $character     The character being blocked from parts of raiding
	 * @param ExportRaidBlockType $blockedFrom   What is disallowed for the blocked character?
	 * @param ?ExportCharacter    $blockedBy     The character who issued the ban
	 * @param ?string             $blockedReason The reason for this block
	 * @param ?int                $blockStart    When was the block issued?
	 * @param ?int                $blockEnd      If the block is temporary, set this to when the block expires
	 */
	public function __construct(
		public ExportCharacter $character,
		public ExportRaidBlockType $blockedFrom,
		public ?ExportCharacter $blockedBy=null,
		public ?string $blockedReason=null,
		public ?int $blockStart=null,
		public ?int $blockEnd=null,
	) {
	}
}
