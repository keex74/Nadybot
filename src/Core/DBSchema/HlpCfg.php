<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes as NCA, DBRow};

#[NCA\DB\Table(name: 'hlpcfg')]
class HlpCfg extends DBRow {
	public function __construct(
		public string $name,
		public string $module,
		public string $file,
		public string $description,
		public string $admin,
		public int $verify=0,
	) {
	}
}
