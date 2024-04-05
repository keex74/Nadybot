<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\ImplantSlot;
use Nadybot\Core\ParamClass\Base;

class PImplantSlot extends Base {
	protected static string $regExp = 'eyes?|ocular'.
		'|head|brain'.
		'|ear'.
		'|right arm|rarm'.
		'|body|chest'.
		'|left arm|larm'.
		'|right wrist|rwrist'.
		'|waist'.
		'|left wrist|lwrist'.
		'|right hand|rhand'.
		'|legs|leg|thigh'.
		'|left hand|lhand'.
		'|foot|feet';
	protected ImplantSlot $value;

	public function __construct(string $value) {
		$this->value = ImplantSlot::byName($value);
	}

	public function __invoke(): ImplantSlot {
		return $this->value;
	}

	public function __toString(): string {
		return $this->value->designSlotName();
	}
}
