<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class RaidBlock {
	/**
	 * @param Character     $character     The character being blocked from parts of raiding
	 * @param RaidBlockType $blockedFrom   What is disallowed for the blocked character?
	 * @param ?Character    $blockedBy     The character who issued the ban
	 * @param ?string       $blockedReason The reason for this block
	 * @param ?int          $blockStart    When was the block issued?
	 * @param ?int          $blockEnd      If the block is temporary, set this to when the block expires
	 */
	public function __construct(
		public Character $character,
		public RaidBlockType $blockedFrom,
		public ?Character $blockedBy=null,
		public ?string $blockedReason=null,
		public ?int $blockStart=null,
		public ?int $blockEnd=null,
	) {
	}
}
