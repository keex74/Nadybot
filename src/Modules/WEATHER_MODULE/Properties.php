<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Nadybot\Core\StringableTrait;

class Properties {
	use StringableTrait;

	/** @param Timeseries[] $timeseries */
	public function __construct(
		public Meta $meta,
		#[CastListToType(Timeseries::class)] public array $timeseries=[],
	) {
	}
}
