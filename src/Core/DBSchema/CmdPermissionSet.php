<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes as NCA, DBRow};

#[NCA\DB\Table(name: 'cmd_permission_set')]
class CmdPermissionSet extends DBRow {
	public function __construct(
		public string $name,
		public string $letter,
	) {
	}
}
