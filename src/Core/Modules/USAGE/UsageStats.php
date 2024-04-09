<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\USAGE;

class UsageStats {
	public function __construct(
		public string $id,
		public object $commands,
		public SettingsUsageStats $settings,
		public int $version=2,
		public bool $debug=false,
	) {
	}
}
