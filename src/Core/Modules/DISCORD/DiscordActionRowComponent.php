<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

class DiscordActionRowComponent extends DiscordComponent {
	public int $type = 1;

	/** @param DiscordComponent[] $components the actual components */
	public function __construct(
		public array $components=[],
	) {
	}
}
