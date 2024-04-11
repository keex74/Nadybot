<?php declare(strict_types=1);

namespace Nadybot\Modules\PRIVATE_CHANNEL_MODULE;

class OrgCount {
	public function __construct(
		public float $avgLevel,
		public int $numPlayers,
		public ?string $orgName=null,
	) {
	}
}
