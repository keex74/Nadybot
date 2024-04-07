<?php declare(strict_types=1);

namespace Nadybot\Modules\GUILD_MODULE;

use Nadybot\Core\Attributes\DB\{AutoInc, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'org_history', shared: Shared::Yes)]
class OrgHistory extends DBRow {
	/**
	 * @param ?string $actor        The person doing the action
	 * @param ?string $actee        Optional, the person the actor is acting on
	 * @param ?string $action       The action the actor is doing
	 * @param ?string $organization Name of the organization this action was done in
	 * @param ?int    $time         Timestamp when the action happened
	 * @param ?int    $id           Internal ID of this history entry
	 */
	public function __construct(
		public ?string $actor,
		public ?string $actee,
		public ?string $action,
		public ?string $organization,
		public ?int $time,
		#[AutoInc] public ?int $id=null,
	) {
	}
}
