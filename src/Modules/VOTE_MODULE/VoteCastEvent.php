<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

class VoteCastEvent extends VoteEvent {
	public const EVENT_MASK = 'vote(cast)';

	public function __construct(
		Poll $poll,
		string $player,
		public string $vote,
	) {
		parent::__construct(poll: $poll, player: $player);
		$this->type = self::EVENT_MASK;
	}
}
