<?php declare(strict_types=1);

namespace Nadybot\Modules\EVENTS_MODULE;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Nadybot\Core\ExportCharacter;

class ExportEvent {
	/**
	 * @param string             $name         Name by which the event is known
	 * @param ?ExportCharacter   $createdBy    Character who created this event
	 * @param ?int               $creationTime Time when this event was created
	 * @param ?int               $startTime    Time when this event starts
	 * @param ?string            $description  Description, what the event is about
	 * @param ?ExportCharacter[] $attendees    List of all attending characters
	 *
	 * @psalm-param ?list<ExportCharacter> $attendees
	 */
	public function __construct(
		public string $name,
		public ?ExportCharacter $createdBy=null,
		public ?int $creationTime=null,
		public ?int $startTime=null,
		public ?string $description=null,
		#[CastListToType(ExportCharacter::class)] public ?array $attendees=null,
	) {
	}
}
