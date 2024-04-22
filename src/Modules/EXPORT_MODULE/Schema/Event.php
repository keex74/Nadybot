<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class Event {
	/**
	 * @param string       $name         Name by which the event is known
	 * @param ?Character   $createdBy    Character who created this event
	 * @param ?int         $creationTime Time when this event was created
	 * @param ?int         $startTime    Time when this event starts
	 * @param ?string      $description  Description, what the event is about
	 * @param ?Character[] $attendees    List of all attending characters
	 *
	 * @psalm-param ?list<Character> $attendees
	 */
	public function __construct(
		public string $name,
		public ?Character $createdBy=null,
		public ?int $creationTime=null,
		public ?int $startTime=null,
		public ?string $description=null,
		#[CastListToType(Character::class)] public ?array $attendees=null,
	) {
	}
}
