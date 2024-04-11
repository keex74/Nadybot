<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

class VoteDelEvent extends VoteEvent {
	public const EVENT_MASK = 'vote(del)';

	public function __construct(
		Poll $poll,
		public string $player,
	) {
		parent::__construct(poll: $poll, player: $player);
		$this->type = self::EVENT_MASK;
	}
}
