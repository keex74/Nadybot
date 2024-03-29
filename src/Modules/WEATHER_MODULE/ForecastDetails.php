<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class ForecastDetails {
	use StringableTrait;

	public function __construct(
		public ?float $precipitation_amount=null,
	) {
	}
}
