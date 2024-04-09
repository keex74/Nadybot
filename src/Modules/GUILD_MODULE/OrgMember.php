<?php declare(strict_types=1);

namespace Nadybot\Modules\GUILD_MODULE;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBTable;

#[Table(name: 'org_members')]
class OrgMember extends DBTable {
	public function __construct(
		public string $name,
		public ?string $mode=null,
		public ?int $logged_off=0,
	) {
	}
}
