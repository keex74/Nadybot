<?php declare(strict_types=1);

namespace Nadybot\Modules\RAFFLE_MODULE;

class RaffleLeaveEvent extends RaffleParticipationEvent {
	public const EVENT_MASK = 'raffle(leave)';

	public function __construct(
		Raffle $raffle,
		string $player,
	) {
		parent::__construct(raffle: $raffle, player: $player);
		$this->type = self::EVENT_MASK;
	}
}
