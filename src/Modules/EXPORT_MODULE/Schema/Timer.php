<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class Timer {
	/**
	 * @param int        $endTime        Time when the timer needs to go off
	 * @param ?int       $startTime      Time when the timer was created
	 * @param ?string    $timerName      Name of the timer, used to refer to it
	 * @param ?Character $createdBy      The character who created the timer
	 * @param ?Channel[] $channels
	 * @param ?int       $repeatInterval If this is a repeating timer, then set this to the number of seconds between each repeat
	 * @param ?Alert[]   $alerts         Alerts that need to be fired by this alarm. If these are set, then endTime doesn't trigger an alarm by itself, but requires the endTime alert to be an own alert
	 */
	public function __construct(
		public int $endTime,
		public ?int $startTime=null,
		public ?string $timerName=null,
		public ?Character $createdBy=null,
		#[CastListToEnums(Channel::class)] public ?array $channels=null,
		#[Min(1)] public ?int $repeatInterval=null,
		#[CastListToType(Alert::class)] public ?array $alerts=null,
	) {
	}
}
