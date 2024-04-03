<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE;

use Nadybot\Core\{Attributes\DB, DBRow};
use Nadybot\Modules\ITEMS_MODULE\Skill;

#[DB\Table(name: 'perk_level_buffs', shared: DB\Shared::Yes)]
class PerkLevelBuff extends DBRow {
	#[DB\Ignore]
	public ?Skill $skill=null;

	public function __construct(
		#[DB\PK] public int $perk_level_id,
		#[DB\PK] public int $skill_id,
		public int $amount,
	) {
	}
}
