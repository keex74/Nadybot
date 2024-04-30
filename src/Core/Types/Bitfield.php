<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

use Stringable;

class Bitfield implements Stringable {
	private int $value = 0;

	public function __toString(): string {
		return (string)$this->value;
	}

	public function has(int $flag): bool {
		return ($this->value & $flag) === $flag;
	}

	public function hasAll(int ...$flags): bool {
		foreach ($flags as $flag) {
			if (($this->value & $flag) !== $flag) {
				return false;
			}
		}
		return true;
	}

	public function hasAny(int ...$flags): bool {
		foreach ($flags as $flag) {
			if (($this->value & $flag) === $flag) {
				return true;
			}
		}
		return false;
	}

	public function setInt(int $value): self {
		$this->value |= $value;
		return $this;
	}

	public function set(int ...$flags): self {
		for ($i = 0; $i < count($flags); $i++) {
			$this->value |= $flags[$i];
		}
		return $this;
	}

	public function toInt(): int {
		return $this->value;
	}
}
