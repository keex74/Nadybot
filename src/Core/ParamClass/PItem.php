<?php declare(strict_types=1);

namespace Nadybot\Core\ParamClass;

use InvalidArgumentException;
use Nadybot\Core\{AOItem, Safe};

class PItem extends Base implements AOItem {
	public int $lowID;
	public int $highID;
	public int $ql;
	public string $name;
	protected static string $regExp = "(?:<|&lt;)a href=(?:&#39;|'|\x22)itemref://\d+/\d+/\d+(?:&#39;|'|\x22)(?:>|&gt;).+?(<|&lt;)/a(>|&gt;)";
	protected string $value;

	public function __construct(string $value) {
		$this->value = htmlspecialchars_decode($value);
		if (!count($matches = Safe::pregMatch("{itemref://(\d+)/(\d+)/(\d+)(?:&#39;|'|\x22)(?:>|&gt;)(.+?)(<|&lt;)/a(>|&gt;)}", $value))) {
			throw new InvalidArgumentException('Item is not matching the item spec');
		}
		$this->lowID = (int)$matches[1];
		$this->highID = (int)$matches[2];
		$this->ql = (int)$matches[3];
		$this->name = $matches[4];
	}

	public function __invoke(): string {
		return $this->value;
	}

	public function __toString(): string {
		return $this->value;
	}

	public function getLowID(): int {
		return $this->lowID;
	}

	public function getHighID(): int {
		return $this->highID;
	}

	public function getLowQL(): int {
		return $this->ql;
	}

	public function getHighQL(): int {
		return $this->ql;
	}

	public function getName(): string {
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
		return $this->ql;
	}

	public function setQL(int $ql): self {
		$this->ql = $ql;
		return $this;
	}
}
