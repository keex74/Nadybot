<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

class ImplantBonusStats {
	public string $slot = 'Faded';

	/** @param int[] $range */
	public function __construct(
		public int $buff,
		public array $range,
		int|string $slot,
	) {
		if (is_string($slot)) {
			$this->slot = $slot;
		} elseif ($slot === ImplantController::FADED) {
			$this->slot = 'Faded';
		} elseif ($slot === ImplantController::BRIGHT) {
			$this->slot = 'Bright';
		} else {
			$this->slot = 'Shiny';
		}
	}
}
