<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Raider {
	/**
	 * @param Character $character The raider
	 * @param ?int      $joinTime  When did the character join the raid?
	 * @param ?int      $leaveTime When did the character leave the raid?
	 */
	public function __construct(
		public Character $character,
		public ?int $joinTime=null,
		public ?int $leaveTime=null,
	) {
	}
}
