<?php declare(strict_types=1);

namespace Nadybot\Modules\ALIEN_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\{DBTable, Types\Profession};
use Ramsey\Uuid\UuidInterface;

#[Table(name: 'leprocs', shared: Shared::Yes)]
class LEProc extends DBTable {
	public function __construct(
		#[PK] public UuidInterface $id,
		public Profession $profession,
		public string $name,
		public string $research_name,
		public int $research_lvl,
		public string $modifiers,
		public string $duration,
		public string $proc_trigger,
		public string $description,
		public int $proc_type=1,
		public int $chance=0,
	) {
	}
}
