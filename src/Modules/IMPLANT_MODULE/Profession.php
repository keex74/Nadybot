<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\{DBTable};

#[Table(name: 'Profession', shared: Shared::Yes)]
class Profession extends DBTable {
	public function __construct(
		public int $ID,
		public string $Name,
	) {
	}
}
