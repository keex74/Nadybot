<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

class ImplantBonusTypes {
	public function __construct(
		public ImplantBonusStats $faded,
		public ImplantBonusStats $bright,
		public ImplantBonusStats $shiny,
	) {
	}
}
