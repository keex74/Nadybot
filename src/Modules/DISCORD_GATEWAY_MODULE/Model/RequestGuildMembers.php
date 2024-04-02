<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE\Model;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Nadybot\Core\Modules\DISCORD\ReducedStringableTrait;
use Stringable;

class RequestGuildMembers implements Stringable {
	use ReducedStringableTrait;

	/** @param string[] $user_ids */
	public function __construct(
		public string $guild_id,
		public ?string $query='',
		public int $limit=0,
		public ?bool $presences=null,
		#[CastListToType('string')] public ?array $user_ids=null,
		public ?string $nonce=null,
	) {
	}
}
