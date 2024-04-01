<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class Role implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param int    $color       integer representation of hexadecimal color code
	 * @param bool   $hoist       if this role is pinned in the user listing
	 * @param string $permissions permission bit set
	 * @param bool   $managed     whether this role is managed by an integration
	 * @param bool   $mentionable whether this role is mentionable
	 */
	public function __construct(
		public string $id,
		public string $name,
		public int $color,
		public bool $hoist,
		public int $position,
		public string $permissions,
		public bool $managed,
		public bool $mentionable,
	) {
	}
}
