<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class Geometry {
	use StringableTrait;

	/**
	 * @param array<float|int> $coordinates
	 *
	 * @psalm-param array{0: float, 1: float, 2: int} $coordinates
	 */
	public function __construct(
		public string $type,
		public array $coordinates,
	) {
	}
}
