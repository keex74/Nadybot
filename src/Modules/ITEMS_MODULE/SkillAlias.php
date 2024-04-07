<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'skill_alias', shared: Shared::Yes)]
class SkillAlias extends DBRow {
	public function __construct(
		public int $id,
		public string $name,
	) {
	}
}
