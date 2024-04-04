<?php declare(strict_types=1);

namespace Nadybot\Modules\BANK_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\{AOItem, DBRow};

#[Table(name: 'bank', shared: Shared::Yes)]
class Bank extends DBRow implements AOItem {
	public function __construct(
		public ?string $name,
		public ?int $lowid,
		public ?int $highid,
		public ?int $ql,
		public ?string $player,
		public ?string $container,
		public ?int $container_id,
		public ?string $location,
	) {
	}

	public function getLowID(): int {
		if (!isset($this->lowid)) {
			throw new \InvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '() cannot return null');
		}
		return $this->lowid;
	}

	public function getHighID(): int {
		if (!isset($this->highid)) {
			throw new \InvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '() cannot return null');
		}
		return $this->highid;
	}

	public function getLowQL(): int {
		if (!isset($this->ql)) {
			throw new \InvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '() cannot return null');
		}
		return $this->ql;
	}

	public function getHighQL(): int {
		if (!isset($this->ql)) {
			throw new \InvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '() cannot return null');
		}
		return $this->ql;
	}

	public function getName(): string {
		if (!isset($this->name)) {
			throw new \InvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '() cannot return null');
		}
		return $this->name;
	}

	public function getLink(?int $ql=null, ?string $text=null): string {
		$ql ??= $this->getQL();
		$text ??= $this->getName();
		return "<a href='itemref://{$this->getLowID()}/{$this->getHighID()}/{$ql}'>{$text}</a>";
	}

	public function atQL(int $ql): static {
		$new = clone $this;
		$new->ql = $ql;
		return $new;
	}

	public function getQL(): int {
		if (!isset($this->ql)) {
			throw new \InvalidArgumentException(__CLASS__ . '::' . __FUNCTION__ . '() cannot return null');
		}
		return $this->ql;
	}

	public function setQL(int $ql): self {
		$this->ql = $ql;
		return $this;
	}
}
