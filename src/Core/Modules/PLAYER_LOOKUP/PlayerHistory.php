<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\PLAYER_LOOKUP;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class PlayerHistory {
	/**
	 * @param PlayerHistoryData[] $data
	 *
	 * @psalm-param list<PlayerHistoryData> $data
	 */
	public function __construct(
		public string $name,
		#[CastListToType(PlayerHistoryData::class)] public array $data=[],
	) {
	}
}
