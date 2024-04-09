<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\BAN;

use Nadybot\Core\{Attributes as NCA, DBTable};

#[NCA\DB\Table(name: 'banned_orgs')]
class BannedOrg extends DBTable {
	/**
	 * @param int    $org_id    The ID of the org that is or was banned
	 * @param string $org_name  The name of the org that is or was banned
	 * @param string $banned_by Name of the person banning the org
	 * @param int    $start     UNIX timestamp when the ban starts
	 * @param string $reason    Reason why the org was banned
	 * @param ?int   $end       If this is a temporary ban, this is the UNIX timestamp when the ban will end
	 */
	public function __construct(
		#[NCA\DB\PK] public int $org_id,
		#[NCA\DB\Ignore] public string $org_name,
		public string $banned_by,
		public int $start,
		public string $reason,
		public ?int $end=null,
	) {
	}
}
