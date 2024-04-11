<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

class ExtBuff {
	public function __construct(
		public Skill $skill,
		public int $amount,
	) {
	}
}
