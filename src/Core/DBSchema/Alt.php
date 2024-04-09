<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\DBTable;

#[Table(name: 'alts', shared: Shared::Yes)]
class Alt extends DBTable {
	public function __construct(
		#[PK] public string $alt,
		public string $main,
		public ?bool $validated_by_main=false,
		public ?bool $validated_by_alt=false,
		public ?string $added_via=null,
	) {
	}
}
