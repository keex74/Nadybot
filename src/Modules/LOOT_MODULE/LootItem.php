<?php declare(strict_types=1);

namespace Nadybot\Modules\LOOT_MODULE;

class LootItem {
	/** @param array<string,bool> $users */
	public function __construct(
		public string $name,
		public string $added_by,
		public string $display,
		public ?int $icon=null,
		public string $comment='',
		public int $multiloot=1,
		public array $users=[],
	) {
	}
}
