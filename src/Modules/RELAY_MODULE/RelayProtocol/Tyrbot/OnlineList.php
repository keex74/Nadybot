<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE\RelayProtocol\Tyrbot;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class OnlineList extends Packet {
	/**
	 * @param OnlineBlock[] $online
	 *
	 * @psalm-param list<OnlineBlock> $online
	 */
	public function __construct(
		public string $type,
		#[CastListToType(OnlineBlock::class)] public array $online,
	) {
	}
}
