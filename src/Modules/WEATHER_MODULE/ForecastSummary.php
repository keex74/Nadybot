<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class ForecastSummary {
	use StringableTrait;

	public function __construct(
		public string $symbol_code,
	) {
	}
}
