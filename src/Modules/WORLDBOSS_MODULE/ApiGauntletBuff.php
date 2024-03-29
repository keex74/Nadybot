<?php declare(strict_types=1);

namespace Nadybot\Modules\WORLDBOSS_MODULE;

use Nadybot\Core\{
	Attributes as NCA,
	Faction,
	StringableTrait
};
use Stringable;

class ApiGauntletBuff implements Stringable {
	use StringableTrait;

	public function __construct(
		#[
			NCA\StrFuncIn('strtolower', 'ucfirst')
		] public Faction $faction,
		public int $expires,
		public int $dimension,
	) {
	}
}
