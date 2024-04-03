<?php declare(strict_types=1);

namespace Nadybot\Modules\WHEREIS_MODULE;

use Illuminate\Support\Collection;
use Nadybot\Core\Attributes\DB\{Ignore, PK, Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'spawntime', shared: Shared::Yes)]
class Spawntime extends DBRow {
	/** @var Collection<Whereis> */
	#[Ignore] public Collection $coordinates;

	/** @param ?Collection<Whereis> $coordinates */
	public function __construct(
		#[PK] public string $mob,
		public ?string $alias=null,
		public ?string $placeholder=null,
		public ?bool $can_skip_spawn=null,
		public ?int $spawntime=null,
		?Collection $coordinates=null,
	) {
		$this->coordinates = $coordinates ?? new Collection();
	}
}
