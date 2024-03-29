<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE;

use Nadybot\Core\{DBRow, Profession};

class PerkLevelProf extends DBRow {
	public function __construct(
		public int $perk_level_id,
		public Profession $profession,
	) {
	}
}
