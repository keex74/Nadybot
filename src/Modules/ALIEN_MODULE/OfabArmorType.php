<?php declare(strict_types=1);

namespace Nadybot\Modules\ALIEN_MODULE;

use Nadybot\Core\{DBRow, Profession};

class OfabArmorType extends DBRow {
	public function __construct(
		public int $type,
		public Profession $profession,
	) {
	}
}
