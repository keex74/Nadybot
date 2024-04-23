<?php declare(strict_types=1);

namespace Nadybot\Modules\BASIC_CHAT_MODULE;

class CallerList {
	/**
	 * @param string       $name    Name of this list of callers, e.g. "RI1", "east", or empty string if default
	 * @param string       $creator Name of the character who created this list
	 * @param list<Caller> $callers List of the characters who are callers
	 */
	public function __construct(
		public string $name,
		public string $creator,
		public array $callers=[],
	) {
	}

	/** @return list<string> */
	public function getNames(): array {
		return array_column($this->callers, 'name');
	}

	/** Check if $name is in this caller list */
	public function isInList(string $name): bool {
		return in_array($name, $this->getNames(), true);
	}

	/** Get the amount of callers on this list */
	public function count(): int {
		return count($this->callers);
	}

	/**
	 * Remove all callers added by $search
	 *
	 * @param string $search       Either the full name or a partial one
	 * @param bool   $partialMatch Do a partial match on $search
	 * @param bool   $invert       if true, remove those NOT matching
	 *
	 * @return list<Caller> The removed callers
	 */
	public function removeCallersAddedBy(string $search, bool $partialMatch, bool $invert): array {
		if (!$partialMatch) {
			$search = ucfirst(strtolower($search));
		}
		$removed = [];
		$this->callers = array_values(
			array_filter(
				$this->callers,
				static function (Caller $caller) use ($search, &$removed, $partialMatch, $invert): bool {
					$remove = false;
					if (!$partialMatch) {
						$remove = $search === $caller->addedBy;
					} elseif (strncasecmp($caller->addedBy, $search, strlen($search)) === 0) {
						$remove = true;
					}
					$remove = $invert ? !$remove : $remove;
					if (!$remove) {
						return true;
					}
					$removed []= $caller;
					return false;
				}
			)
		);
		return $removed;
	}
}
