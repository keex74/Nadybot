<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class DiscordActionRowComponent extends DiscordComponent {
	public int $type = 1;

	/**
	 * @param DiscordComponent[] $components the actual components
	 *
	 * @psalm-param list<DiscordComponent> $components
	 */
	public function __construct(
		#[CastListToType(DiscordComponent::class)] public array $components=[],
	) {
	}
}
