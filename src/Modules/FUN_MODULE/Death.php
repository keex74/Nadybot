<?php declare(strict_types=1);

namespace Nadybot\Modules\FUN_MODULE;

use Nadybot\Core\{Attributes as NCA, DBTable};

#[NCA\DB\Table(name: 'death')]
class Death extends DBTable {
	public function __construct(
		#[NCA\DB\PK] public string $character,
		public int $counter=0,
	) {
	}
}
