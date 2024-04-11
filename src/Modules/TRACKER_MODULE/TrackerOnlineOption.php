<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE;

class TrackerOnlineOption {
	public function __construct(
		public string $type,
		public string $value,
	) {
	}
}
