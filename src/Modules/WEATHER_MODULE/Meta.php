<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class Meta {
	use StringableTrait;

	public function __construct(
		public string $updated_at,
		public Units $units,
	) {
	}
}
