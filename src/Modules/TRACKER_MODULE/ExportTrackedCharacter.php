<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Nadybot\Core\ExportCharacter;

class ExportTrackedCharacter {
	/**
	 * @param ExportCharacter       $character The character we are tracking
	 * @param ?int                  $addedTime Time when we started tracking this character
	 * @param ?ExportCharacter      $addedBy   The character who added this character to the tracking list
	 * @param ?ExportTrackerEvent[] $events
	 *
	 * @psalm-param ?list<ExportTrackerEvent> $events
	 */
	public function __construct(
		public ExportCharacter $character,
		public ?int $addedTime=null,
		public ?ExportCharacter $addedBy=null,
		#[CastListToType(ExportTrackerEvent::class)] public ?array $events=null,
	) {
	}
}
