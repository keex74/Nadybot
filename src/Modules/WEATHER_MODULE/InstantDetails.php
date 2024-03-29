<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class InstantDetails {
	use StringableTrait;

	public function __construct(
		public float $air_pressure_at_sea_level,
		public float $air_temperature,
		public float $cloud_area_fraction,
		public float $relative_humidity,
		public float $wind_from_direction,
		public float $wind_speed,
		public ?float $dew_point_temperature=null,
	) {
	}
}
