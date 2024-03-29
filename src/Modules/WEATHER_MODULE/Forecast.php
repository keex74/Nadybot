<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class Forecast {
	use StringableTrait;

	public function __construct(
		public ForecastSummary $summary,
		public ?ForecastDetails $details=null,
	) {
	}
}
