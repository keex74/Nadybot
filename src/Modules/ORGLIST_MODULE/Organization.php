<?php declare(strict_types=1);

namespace Nadybot\Modules\ORGLIST_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\{DBRow, Faction, Government};

#[Table(name: 'organizations', shared: Shared::Yes)]
class Organization extends DBRow {
	public function __construct(
		#[PK] public int $id,
		public string $name='Illegal Org',
		public int $num_members=0,
		public Faction $faction=Faction::Neutral,
		public Government $governing_form=Government::Anarchism,
		public string $index='others',
	) {
	}
}
