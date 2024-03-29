<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class Properties {
	use StringableTrait;

	/** @param Timeseries[] $timeseries */
	public function __construct(
		public Meta $meta,
		public array $timeseries=[],
	) {
	}
}
