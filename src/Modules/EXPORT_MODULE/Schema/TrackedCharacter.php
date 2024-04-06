<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class TrackedCharacter {
	/**
	 * @param Character       $character The character we are tracking
	 * @param ?int            $addedTime Time when we started tracking this character
	 * @param ?Character      $addedBy   The character who added this character to the tracking list
	 * @param ?TrackerEvent[] $events
	 */
	public function __construct(
		public Character $character,
		public ?int $addedTime=null,
		public ?Character $addedBy=null,
		#[CastListToType(TrackerEvent::class)] public ?array $events=null,
	) {
	}
}
