<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class MessageActivity implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param ?string $party_id party_id from a Rich Presence event
	 * @param int     $type     the activity's type
	 */
	public function __construct(
		public ?string $party_id=null,
		public int $type=Activity::ACTIVITY_GAME,
	) {
	}
}
