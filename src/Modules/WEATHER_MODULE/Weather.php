<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class Weather {
	use StringableTrait;

	public function __construct(
		public string $type,
		public Geometry $geometry,
		public Properties $properties,
	) {
	}
}
