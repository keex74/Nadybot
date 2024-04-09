<?php declare(strict_types=1);

namespace Nadybot\Modules\GUILD_MODULE;

class RecentOrgMember extends OrgMember {
	public function __construct(
		public string $main,
		string $name,
		?string $mode=null,
		?int $logged_off=0,
	) {
		parent::__construct(name: $name, mode: $mode, logged_off: $logged_off);
	}
}
