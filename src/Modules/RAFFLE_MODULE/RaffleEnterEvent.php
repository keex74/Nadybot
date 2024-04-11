<?php declare(strict_types=1);

namespace Nadybot\Modules\RAFFLE_MODULE;

class RaffleEnterEvent extends RaffleParticipationEvent {
	public const EVENT_MASK = 'raffle(enter)';

	public function __construct(
		Raffle $raffle,
		public string $player,
	) {
		parent::__construct(raffle: $raffle, player: $player);
		$this->type = self::EVENT_MASK;
	}
}
