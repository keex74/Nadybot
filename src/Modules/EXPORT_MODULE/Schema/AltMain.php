<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class AltMain {
	/**
	 * @param Character $main The main character.
	 * @param AltChar[] $alts
	 *
	 * @psalm-param list<AltChar> $alts
	 */
	public function __construct(
		public Character $main,
		#[CastListToType(AltChar::class)] public array $alts,
	) {
	}
}
