<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\{Attributes as NCA, DBTable, Types\AOItem};

#[NCA\DB\Table(name: 'Symbiant', shared: NCA\DB\Shared::Yes)]
class Symbiant extends DBTable implements AOItem {
	public function __construct(
		#[NCA\DB\PK] public int $ID,
		public string $Name,
		public int $QL,
		public int $SlotID,
		public int $TreatmentReq,
		public int $LevelReq,
		public string $Unit,
		#[NCA\DB\Ignore] public string $SlotName,
		#[NCA\DB\Ignore] public string $SlotLongName,
	) {
	}

	public function getLowID(): int {
		return $this->ID;
	}

	public function getHighID(): int {
		return $this->ID;
	}

	public function getLowQL(): int {
		return $this->QL;
	}

	public function getHighQL(): int {
		return $this->QL;
	}

	public function getName(): string {
		return $this->Name;
	}

	public function getLink(?int $ql=null, ?string $text=null): string {
		$ql ??= $this->getQL();
		$text ??= $this->getName();
		return "<a href='itemref://{$this->getLowID()}/{$this->getHighID()}/{$ql}'>{$text}</a>";
	}

	public function atQL(int $ql): static {
		$new = clone $this;
		$new->QL = $ql;
		return $new;
	}

	public function getQL(): int {
		return $this->QL;
	}

	public function setQL(int $ql): self {
		$this->QL = $ql;
		return $this;
	}
}
