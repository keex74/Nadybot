<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

class PollStartEvent extends PollEvent {
	public const EVENT_MASK = 'poll(start)';

	/** @param list<Vote> $votes */
	public function __construct(
		Poll $poll,
		array $votes=[],
	) {
		parent::__construct(poll: $poll, votes: $votes);
		$this->type = self::EVENT_MASK;
	}
}
