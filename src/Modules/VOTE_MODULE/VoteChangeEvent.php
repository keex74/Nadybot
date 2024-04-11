<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

class VoteChangeEvent extends VoteEvent {
	public const EVENT_MASK = 'vote(change)';

	public function __construct(
		Poll $poll,
		string $player,
		public string $vote,
		public string $oldVote,
	) {
		parent::__construct(poll: $poll, player: $player);
		$this->type = self::EVENT_MASK;
	}
}
