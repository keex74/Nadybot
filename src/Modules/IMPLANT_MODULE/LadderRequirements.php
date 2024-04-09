<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'implant_requirements', shared: Shared::Yes)]
class LadderRequirements extends DBTable {
	public function __construct(
		#[PK] public int $ql,
		public int $treatment,
		public int $ability,
		public int $abilityShiny,
		public int $abilityBright,
		public int $abilityFaded,
		public int $skillShiny,
		public int $skillBright,
		public int $skillFaded,
		public int $lowestAbilityShiny,
		public int $lowestAbilityBright,
		public int $lowestAbilityFaded,
		public int $lowestSkillShiny,
		public int $lowestSkillBright,
		public int $lowestSkillFaded,
	) {
	}
}
