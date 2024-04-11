<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE;

class PerkAggregate {
	/**
	 * @param string            $name        Name of the perk
	 * @param string[]          $professions
	 * @param ?string           $description An optional description of the perk
	 * @param string            $expansion   The expansion needed for this perk
	 * @param array<int,int>    $buffs
	 * @param array<int,int>    $resistances
	 * @param PerkLevelAction[] $actions
	 */
	public function __construct(
		public string $name,
		public array $professions,
		public ?string $description=null,
		public string $expansion='sl',
		public int $max_level=1,
		public array $buffs=[],
		public array $resistances=[],
		public array $actions=[],
	) {
	}
}
