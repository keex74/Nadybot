<?php declare(strict_types=1);

namespace Nadybot\Modules\PACKAGE_MODULE;

class PackageRequirement {
	/**
	 * @param string $name    Name of the module/extension that's required
	 * @param string $version The required version
	 */
	public function __construct(
		public string $name,
		public string $version,
	) {
	}
}
