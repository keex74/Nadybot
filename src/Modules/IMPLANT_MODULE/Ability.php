<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'Ability', shared: Shared::Yes)]
class Ability extends DBTable {
	public function __construct(
		public int $AbilityID,
		public string $Name,
	) {
	}
}
