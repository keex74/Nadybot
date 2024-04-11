<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

class PollDelEvent extends PollEvent {
	public const EVENT_MASK = 'poll(del)';

	/** @param Vote[] $votes */
	public function __construct(
		Poll $poll,
		array $votes,
	) {
		parent::__construct(poll: $poll, votes: $votes);
		$this->type = self::EVENT_MASK;
	}
}
