<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\Attributes\DB\Shared;
use Nadybot\Core\{Attributes as NCA, DBTable};

#[NCA\DB\Table(name: 'last_online', shared: Shared::Yes)]
class LastOnline extends DBTable {
	/**
	 * @param int    $uid  uid of the character
	 * @param string $name name of the character
	 * @param int    $dt   Timestamp when $name was last online
	 * @param string $main name of the main character
	 */
	public function __construct(
		#[NCA\DB\PK] public int $uid,
		public string $name,
		public int $dt,
		#[NCA\DB\Ignore] public ?string $main=null,
	) {
	}
}
