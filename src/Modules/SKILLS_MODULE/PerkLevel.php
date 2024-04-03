<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE;

use Nadybot\Core\{Attributes\DB, DBRow};

#[DB\Table(name: 'perk_level', shared: DB\Shared::Yes)]
class PerkLevel extends DBRow {
	/**
	 * @param int                   $perk_id          The internal ID of the perk line
	 * @param int                   $perk_level       Which level of $perk_id does this represent?
	 * @param int                   $required_level   Required character level to perk this perk level
	 * @param ?int                  $aoid             The internal ID of the perk level in AO
	 * @param string[]              $professions
	 * @param array<int,int>        $buffs
	 * @param ExtPerkLevelBuff[]    $perk_buffs
	 * @param array<int,int>        $resistances
	 * @param PerkLevelResistance[] $perk_resistances
	 */
	public function __construct(
		public int $perk_id,
		public int $perk_level,
		public int $required_level,
		#[DB\AutoInc] public ?int $id=null,
		public ?int $aoid=null,
		#[DB\Ignore] public array $professions=[],
		#[DB\Ignore] public array $buffs=[],
		#[DB\Ignore] public array $perk_buffs=[],
		#[DB\Ignore] public array $resistances=[],
		#[DB\Ignore] public array $perk_resistances=[],
		#[DB\Ignore] public ?PerkLevelAction $action=null,
	) {
	}
}
