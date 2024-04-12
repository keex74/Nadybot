<?php declare(strict_types=1);

namespace Nadybot\Modules\DISC_MODULE;

use Nadybot\Core\Attributes\DB\{PK, Shared, Table};
use Nadybot\Core\{DBTable, Types\AOItem};

#[Table(name: 'discs', shared: Shared::Yes)]
class Disc extends DBTable implements AOItem {
	public function __construct(
		#[PK] public int $disc_id,
		public int $crystal_id,
		public int $crystal_ql,
		public int $disc_ql,
		public string $disc_name,
		public string $crystal_name,
		public ?string $comment=null,
	) {
	}

	public function getLowID(): int {
		return $this->disc_id;
	}

	public function getHighID(): int {
		return $this->disc_id;
	}

	public function getLowQL(): int {
		return $this->disc_ql;
	}

	public function getHighQL(): int {
		return $this->disc_ql;
	}

	public function getName(): string {
		return $this->disc_name;
	}

	public function getLink(?int $ql=null, ?string $text=null): string {
		$ql ??= $this->getQL();
		$text ??= $this->getName();
		return "<a href='itemref://{$this->getLowID()}/{$this->getHighID()}/{$ql}'>{$text}</a>";
	}

	public function getCrystalLink(): string {
		$ql = $this->crystal_ql;
		$text = $this->crystal_name;
		return "<a href='itemref://{$this->crystal_id}/{$this->crystal_id}/{$ql}'>{$text}</a>";
	}

	public function atQL(int $ql): static {
		$new = clone $this;
		$new->disc_ql = $ql;
		return $new;
	}

	public function getQL(): int {
		return $this->disc_ql;
	}

	public function setQL(int $ql): self {
		$this->disc_ql = $ql;
		return $this;
	}
}
