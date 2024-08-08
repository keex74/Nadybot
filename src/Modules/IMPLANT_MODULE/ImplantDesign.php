<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\{DBTable};

#[Table(name: 'implant_design', shared: Shared::Yes)]
class ImplantDesign extends DBTable {
	public function __construct(
		public string $name,
		public string $owner,
		public ?int $dt=null,
		public ?string $design=null,
	) {
	}
}
