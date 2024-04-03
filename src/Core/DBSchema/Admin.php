<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBRow;

#[Table(name: 'admin')]
class Admin extends DBRow {
	public function __construct(
		public string $name,
		public ?int $adminlevel=0,
	) {
	}
}
