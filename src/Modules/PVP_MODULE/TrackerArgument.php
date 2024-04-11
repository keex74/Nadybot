<?php declare(strict_types=1);

namespace Nadybot\Modules\PVP_MODULE;

class TrackerArgument {
	/**
	 * @param string $name  The name of the argument
	 * @param string $value The value of the argument
	 */
	public function __construct(
		public string $name,
		public string $value,
	) {
	}
}
