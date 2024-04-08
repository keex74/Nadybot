<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\CONFIG;

class SettingOption {
	/**
	 * @param string     $name  Name of this option for displaying
	 * @param int|string $value Which value does this option represent?
	 */
	public function __construct(
		public string $name,
		public int|string $value,
	) {
	}
}
