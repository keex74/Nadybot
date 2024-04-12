<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;
use Nadybot\Core\Types\{AOIcon, AOItemSpec};

#[Table(name: 'aodb', shared: Shared::Yes)]
class AODBEntry extends DBTable implements AOItemSpec, AOIcon {
	public function __construct(
		public int $lowid,
		public int $highid,
		public int $lowql,
		public int $highql,
		public string $name,
		public int $icon,
		public int $slot,
		public int $flags,
		public bool $in_game,
		public bool $froob_friendly=false,
	) {
	}

	public function getLink(?int $ql=null, ?string $text=null): string {
		$ql ??= $this->lowql;
		$text ??= $this->name;
		return "<a href='itemref://{$this->lowid}/{$this->highid}/{$ql}'>{$text}</a>";
	}

	public function getLowID(): int {
		return $this->lowid;
	}

	public function getHighID(): int {
		return $this->highid;
	}

	public function getLowQL(): int {
		return $this->lowql;
	}

	public function getHighQL(): int {
		return $this->highql;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getIconID(): int {
		return $this->icon;
	}

	public function getIcon(): string {
		return "<img src=rdb://{$this->getIconID()}>";
	}

	public function atQL(int $ql): AODBItem {
		return new AODBItem(
			ql: $ql,
			lowid: $this->lowid,
			highid: $this->highid,
			lowql: $this->lowql,
			highql: $this->highql,
			name: $this->name,
			icon: $this->icon,
			slot: $this->slot,
			flags: $this->flags,
			in_game: $this->in_game,
			froob_friendly: $this->froob_friendly,
		);
	}
}
