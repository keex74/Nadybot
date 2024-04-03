<?php declare(strict_types=1);

namespace Nadybot\Modules\GUILD_MODULE;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBRow;

#[Table(name: 'org_rank_mapping')]
class OrgRankMapping extends DBRow {
	public function __construct(
		public string $access_level,
		public int $min_rank,
	) {
	}
}
