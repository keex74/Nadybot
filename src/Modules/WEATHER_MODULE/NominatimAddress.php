<?php declare(strict_types=1);

namespace Nadybot\Modules\WEATHER_MODULE;

use Nadybot\Core\StringableTrait;

class NominatimAddress {
	use StringableTrait;

	public function __construct(
		public ?string $country=null,
		public ?string $country_code=null,
		public ?string $state=null,
		public ?string $county=null,
		public ?string $suburb=null,
		public ?string $town=null,
		public ?string $postcode=null,
	) {
	}
}
