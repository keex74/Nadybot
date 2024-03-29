<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\PLAYER_LOOKUP;

class PlayerHistory {
	/** @param \Nadybot\Core\Modules\PLAYER_LOOKUP\PlayerHistoryData[] $data */
	public function __construct(
		public string $name,
		public array $data=[],
	) {
	}
}
