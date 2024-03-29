<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class Units {
	use StringableTrait;

	public function __construct(
		public string $air_pressure_at_sea_level,
		public string $air_temperature,
		public string $cloud_area_fraction,
		public string $precipitation_amount,
		public string $relative_humidity,
		public string $wind_from_direction,
		public string $wind_speed,
	) {
	}
}
