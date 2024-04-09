<?php declare(strict_types=1);

namespace Nadybot\Modules\QUOTE_MODULE;

use Nadybot\Core\{Attributes as NCA, DBTable};

#[NCA\DB\Table(name: 'quote', shared: NCA\DB\Shared::Yes)]
class Quote extends DBTable {
	public function __construct(
		public string $poster,
		public int $dt,
		public string $msg,
		#[NCA\DB\AutoInc] public ?int $id=null,
	) {
	}
}
