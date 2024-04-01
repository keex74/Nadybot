<?php declare(strict_types=1);

namespace Nadybot\Modules\GSP_MODULE;

class Song {
	public function __construct(
		public string $date,
		public string $artwork,
		public ?int $duration=null,
		public string $artist='Unknown Artist',
		public string $title='Unknown Song',
	) {
	}
}
