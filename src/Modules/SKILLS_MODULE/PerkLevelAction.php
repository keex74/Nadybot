<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE;

use Nadybot\Core\{Attributes\DB, DBRow};
use Nadybot\Modules\ITEMS_MODULE\AODBEntry;

#[DB\Table(name: 'perk_level_actions', shared: DB\Shared::Yes)]
class PerkLevelAction extends DBRow {
	public function __construct(
		#[DB\Ignore] public ?int $perk_level,
		public int $action_id,
		public bool $scaling=false,
		#[DB\Ignore] public ?AODBEntry $aodb=null,
		public ?int $perk_level_id=null,
		#[DB\AutoInc] public ?int $id=null,
	) {
	}
}
