<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

use BackedEnum;
use InvalidArgumentException;
use Stringable;

/** @template T of BackedEnum */
class Bitfield implements Stringable {
	/** @var list<BackedEnum> */
	private array $flags = [];
	private int $value = 0;

	/** @param class-string<T> $class */
	public function __construct(private string $class) {
	}

	public function __toString(): string {
		return implode('|', array_map(static fn (BackedEnum $e): string => $e->name, $this->flags));
	}

	public function has(int|BackedEnum $e): bool {
		$flag = is_int($e) ? $e : (int)$e->value;
		return ($this->value & $flag) !== 0;
	}

	public function hasAll(int|BackedEnum ...$flags): bool {
		foreach ($flags as $flag) {
			$flag = is_int($flag) ? $flag : (int)$flag->value;
			if (($this->value & $flag) !== $flag) {
				return false;
			}
		}
		return true;
	}

	public function hasAny(int|BackedEnum ...$flags): bool {
		foreach ($flags as $flag) {
			$flag = is_int($flag) ? $flag : (int)$flag->value;
			if (($this->value & $flag) !== 0) {
				return true;
			}
		}
		return false;
	}

	/** @return self<T> */
	public function setInt(int $value): self {
		$class = $this->class;
		foreach ($class::cases() as $case) {
			if (((int)$case->value & $value) !== 0) {
				$this->set($case);
			}
		}
		return $this;
	}

	/**
	 * @param T ...$enums
	 *
	 * @return self<T>
	 */
	public function set(BackedEnum ...$enums): self {
		for ($i = 0; $i < count($enums); $i++) {
			if (!is_a($enums[$i], $this->class, false)) {
				$i++;
				throw new InvalidArgumentException(
					__CLASS__ . '::' . __FUNCTION__ . "(): Argument #{$i} must be a {$this->class}"
				);
			}
			$value = (int)$enums[$i]->value;
			if (($this->value & $value) !== $value) {
				$this->value |= $value;
				$this->flags []= $enums[$i];
			}
		}
		return $this;
	}

	public function toInt(): int {
		return $this->value;
	}
}
