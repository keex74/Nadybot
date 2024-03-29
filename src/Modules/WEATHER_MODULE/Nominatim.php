<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class Nominatim {
	use StringableTrait;

	/**
	 * @param string[]            $boundingbox
	 * @param array<string,mixed> $namedetails
	 * @param array<string,mixed> $extratags
	 */
	public function __construct(
		public string $lat,
		public string $lon,
		public string $display_name,
		public array $boundingbox,
		public int $place_id,
		public string $licence,
		public string $osm_type,
		public int $osm_id,
		public array $namedetails,
		public string $type,
		public string $category,
		public NominatimAddress $address,
		public array $extratags=[],
	) {
	}
}
