<?php declare(strict_types=1);

namespace EventSauce\ObjectHydrator;

use IteratorAggregate;
use Traversable;

/**
 * @template T
 *
 * @implements IteratorAggregate<T>
 */
class IterableList implements IteratorAggregate {
	/** @return list<T> */
	public function toArray(): array {
	}

	/** @return Traversable<T> */
	public function getIterator(): Traversable {
	}
}
