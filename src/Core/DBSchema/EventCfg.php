<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes as NCA, DBRow};

#[NCA\DB\Table(name: 'eventcfg')]
class EventCfg extends DBRow {
	public function __construct(
		#[NCA\DB\PK] public string $module,
		#[NCA\DB\PK] public string $type,
		#[NCA\DB\PK] public string $file,
		public string $description,
		public int $verify=0,
		public int $status=0,
		public ?string $help=null,
	) {
	}
}
