<?php declare(strict_types=1);

namespace Nadybot\Modules\WHOIS_MODULE;

class CharData {
	public function __construct(
		public string $name,
		public int $charid,
	) {
	}
}
