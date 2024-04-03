<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes as NCA, DBRow};

#[NCA\DB\Table(name: 'members')]
class Member extends DBRow {
	public int $joined;

	public function __construct(
		#[NCA\DB\PK] public string $name,
		public int $autoinv=0,
		?int $joined=null,
		public ?string $added_by=null,
	) {
		$this->joined = $joined ?? time();
	}
}
