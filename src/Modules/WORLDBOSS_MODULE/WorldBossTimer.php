<?php declare(strict_types=1);

namespace Nadybot\Modules\WORLDBOSS_MODULE;

use Nadybot\Core\Attributes\DB\{Ignore, PK, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'worldboss_timers')]
class WorldBossTimer extends DBRow {
	public int $time_submitted;

	public function __construct(
		#[PK] public string $mob_name,
		public int $spawn,
		public int $killable,
		public string $submitter_name,
		?int $time_submitted=null,
		public ?int $timer=null,
		#[Ignore] public ?int $next_spawn=null,
		#[Ignore] public ?int $next_killable=null,
	) {
		$this->time_submitted = $time_submitted ?? time();
	}
}
