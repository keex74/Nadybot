<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

use Nadybot\Core\Event;

abstract class VoteEvent extends Event {
	public const EVENT_MASK = 'vote(*)';

	public function __construct(
		public Poll $poll,
		public string $player,
	) {
		$this->type = self::EVENT_MASK;
	}
}
