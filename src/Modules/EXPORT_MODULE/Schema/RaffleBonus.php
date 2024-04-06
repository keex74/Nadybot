<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class RaffleBonus {
	/**
	 * @param Character $character   The character with a raffle bonus
	 * @param float     $raffleBonus The bonus (or malus) to apply to the next raffle roll
	 */
	public function __construct(
		public Character $character,
		public float $raffleBonus,
	) {
	}
}
