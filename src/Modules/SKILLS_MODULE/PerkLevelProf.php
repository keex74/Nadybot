<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\{DBTable, Profession};

#[Table(name: 'perk_level_prof', shared: Shared::Yes)]
class PerkLevelProf extends DBTable {
	public function __construct(
		public int $perk_level_id,
		public Profession $profession,
	) {
	}
}
