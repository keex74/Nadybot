<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class Raid {
	/**
	 * @param ?int         $raidId               The internal raid number, used so it can be referenced by the auctions
	 * @param ?int         $time                 When did the raid start?
	 * @param ?string      $raidDescription      What was raided?
	 * @param ?bool        $raidLocked           Is/was the raid locked and only raid leaders were allowed to add raiders?
	 * @param ?int         $raidAnnounceInterval How many seconds between announcing the raid?
	 * @param ?int         $raidSecondsPerPoint  If this is set, then raiders are automatically awarded raid points and this is the interval between receiving 1 point.
	 * @param ?Raider[]    $raiders              A list of all raiders who, at one point, were in the raid
	 * @param ?RaidState[] $history              A list of all raiders who, at one point, were in the raid
	 *
	 * @psalm-param ?list<Raider>    $raiders
	 * @psalm-param ?list<RaidState> $history
	 */
	public function __construct(
		public ?int $raidId=null,
		public ?int $time=null,
		public ?string $raidDescription=null,
		public ?bool $raidLocked=null,
		public ?int $raidAnnounceInterval=null,
		public ?int $raidSecondsPerPoint=null,
		#[CastListToType(Raider::class)] public ?array $raiders=null,
		#[CastListToType(RaidState::class)] public ?array $history=null,
	) {
	}
}
