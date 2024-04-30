<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBTable;
use Nadybot\Core\Types\{AOIcon, AOItemSpec, Bitfield, CarrySlot, ItemFlag, WearSlot};

#[Table(name: 'aodb', shared: Shared::Yes)]
class AODBEntry extends DBTable implements AOItemSpec, AOIcon {
	/** @var Bitfield<ItemFlag> */
	public Bitfield $flags;

	/** @var Bitfield<WearSlot>|Bitfield<CarrySlot> */
	public Bitfield $slot;

	public function __construct(
		public int $lowid,
		public int $highid,
		public int $lowql,
		public int $highql,
		public string $name,
		public int $icon,
		int $slot,
		int $flags,
		public bool $in_game,
		public AodbType $type,
		public bool $froob_friendly=false,
	) {
		$this->flags = (new Bitfield(ItemFlag::class))->setInt($flags);
		if ($this->type === AodbType::Armor) {
			$this->slot = (new Bitfield(WearSlot::class))->setInt($slot);
		} elseif ($this->type === AodbType::Weapon) {
			$this->slot = (new Bitfield(CarrySlot::class))->setInt($slot);
		}
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
			slot: $this->slot->toInt(),
			flags: $this->flags->toInt(),
			in_game: $this->in_game,
			type: $this->type,
			froob_friendly: $this->froob_friendly,
		);
	}
}
