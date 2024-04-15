<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'implant_requirements', shared: Shared::Yes)]
class LadderRequirements extends DBTable {
	public int $lowestAbilityShiny = -1;
	public int $lowestAbilityBright = -1;
	public int $lowestAbilityFaded = -1;
	public int $lowestSkillShiny = -1;
	public int $lowestSkillBright = -1;
	public int $lowestSkillFaded = -1;
	public int $highestAbilityShiny = -1;
	public int $highestAbilityBright = -1;
	public int $highestAbilityFaded = -1;
	public int $highestSkillShiny = -1;
	public int $highestSkillBright = -1;
	public int $highestSkillFaded = -1;

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
	) {
	}
}
