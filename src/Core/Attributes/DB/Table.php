<?php declare(strict_types=1);

namespace Nadybot\Core\Attributes\DB;

use Attribute;

/**
 * Define attributes of the table that this class represents
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Table {
	public function __construct(
		public readonly string $name,
		public readonly Shared $shared=Shared::No,
	) {
	}

	public function getName(): string {
		if ($this->shared === Shared::Yes || str_starts_with($this->name, '<')) {
			return $this->name;
		}
		return "{$this->name}_<myname>";
	}
}
