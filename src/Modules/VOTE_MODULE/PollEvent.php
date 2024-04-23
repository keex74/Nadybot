<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

use Nadybot\Core\Events\Event;

class PollEvent extends Event {
	public const EVENT_MASK = 'poll(*)';

	/** @param list<Vote> $votes */
	public function __construct(
		public Poll $poll,
		public array $votes,
	) {
		$this->type = self::EVENT_MASK;
	}
}
