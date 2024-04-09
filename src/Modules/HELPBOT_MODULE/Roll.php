<?php declare(strict_types=1);

namespace Nadybot\Modules\HELPBOT_MODULE;

use Nadybot\Core\{Attributes as NCA, DBTable};

#[NCA\DB\Table(name: 'roll', shared: NCA\DB\Shared::Yes)]
class Roll extends DBTable {
	public function __construct(
		public ?int $time,
		public ?string $name,
		public ?string $options,
		public ?string $result,
		#[NCA\DB\AutoInc] public ?int $id=null,
	) {
	}
}
