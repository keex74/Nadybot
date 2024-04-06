<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes as NCA, DBRow};

#[NCA\DB\Table(name: 'banlist')]
class BanEntry extends DBRow {
	#[NCA\DB\Ignore] public ?string $name=null;

	/**
	 * @param int     $charid uid of the banned person
	 * @param ?string $admin  Name of the person who banned $charid
	 * @param ?int    $time   Unix time stamp when $charid was banned
	 * @param ?string $reason Reason why $charid was banned
	 * @param ?int    $banend Unix timestamp when the ban ends, or null/0 if never
	 */
	public function __construct(
		#[NCA\DB\PK] public int $charid,
		public ?string $admin=null,
		public ?int $time=null,
		public ?string $reason=null,
		public ?int $banend=null,
	) {
	}
}
