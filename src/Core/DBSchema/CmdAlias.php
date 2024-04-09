<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBTable;

#[Table(name: 'cmd_alias')]
class CmdAlias extends DBTable {
	public function __construct(
		public string $cmd,
		public string $alias,
		public ?string $module=null,
		public int $status=0,
	) {
	}
}
