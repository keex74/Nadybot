<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\PLAYER_LOOKUP;

use DateTimeImmutable;
use EventSauce\ObjectHydrator\PropertyCasters\{CastToDateTimeImmutable, CastToType};
use Nadybot\Core\Types\Faction;

class PlayerHistoryData {
	public function __construct(
		public string $nickname,
		#[CastToType('int')] public int $level,
		public string $breed,
		public string $gender,
		#[CastToType('int')] public int $defender_rank,
		public ?string $guild_rank_name,
		public ?string $guild_name,
		#[CastToType('int')] #[CastToDateTimeImmutable] public DateTimeImmutable $last_changed,
		public Faction $faction,
		#[CastToType('bool')] public bool $deleted=false,
	) {
	}
}
