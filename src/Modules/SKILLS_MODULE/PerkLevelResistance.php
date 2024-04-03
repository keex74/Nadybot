<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE;

use Nadybot\Core\{Attributes\DB, DBRow};

#[DB\Table(name: 'perk_level_resistances', shared: DB\Shared::Yes)]
class PerkLevelResistance extends DBRow {
	#[DB\Ignore] public int $perk_level;

	#[DB\Ignore] public ?string $nanoline=null;

	public function __construct(
		#[DB\PK] public int $perk_level_id,
		#[DB\PK] public int $strain_id,
		public int $amount,
	) {
	}
}
