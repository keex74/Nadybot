<?php declare(strict_types=1);

namespace EventSauce\ObjectHydrator;

use IteratorAggregate;

/**
 * @template T
 */
class IterableList implements IteratorAggregate {
	/** @return list<T> */
	public function toArray(): array {
		return [];
	}
}
