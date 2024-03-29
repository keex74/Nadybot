<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class WeatherData {
	use StringableTrait;

	public function __construct(
		public Instant $instant,
		public ?Forecast $next_1_hours=null,
		public ?Forecast $next_6_hours=null,
		public ?Forecast $next_12_hours=null,
	) {
	}
}
