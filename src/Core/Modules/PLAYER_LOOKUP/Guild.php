<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\PLAYER_LOOKUP;

use Nadybot\Core\DBSchema\Player;
use Nadybot\Core\Types\{Faction, Government};
use Safe\DateTimeImmutable;

class Guild {
	/**
	 * @param array<string,Player> $members
	 * @param ?DateTimeImmutable   $last_update When was the guild information last updated on PORK
	 */
	public function __construct(
		public int $guild_id,
		public string $orgname,
		public Faction $orgside,
		public Government $governing_form=Government::Anarchism,
		public array $members=[],
		public ?DateTimeImmutable $last_update=null,
	) {
	}

	public function getColorName(): string {
		return $this->orgside->inColor($this->orgname);
	}
}
