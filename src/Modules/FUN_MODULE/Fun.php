<?php declare(strict_types=1);

namespace Nadybot\Modules\FUN_MODULE;

use Nadybot\Core\{Attributes as NCA, DBRow};

#[NCA\DB\Table(name: 'fun', shared: NCA\DB\Shared::Yes)]
class Fun extends DBRow {
	public function __construct(
		public string $type,
		public string $content,
		#[NCA\DB\AutoInc] public ?int $id=null,
	) {
	}
}
