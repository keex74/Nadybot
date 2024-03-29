<?php declare(strict_types=1);

namespace Nadybot\Modules\GSP_MODULE;

class Stream {
	public function __construct(
		public string $id,
		public string $url,
		public int $bitrate,
		public string $codec,
	) {
	}
}
