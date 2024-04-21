<?php declare(strict_types=1);

namespace Illuminate\Support;

use ArrayAccess;
use Illuminate\Contracts\Support\CanBeEscapedWhenCastToString;

/**
 * @template TKey of array-key
 *
 * @template-covariant TValue
 *
 * @implements \ArrayAccess<TKey, TValue>
 * @implements \Illuminate\Support\Enumerable<TKey, TValue>
 *
 * @method list<TValue> toList()
 *
 * @phpstan-ignore-next-line
 */
class Collection implements ArrayAccess, CanBeEscapedWhenCastToString, Enumerable {
	/**
	 * Get the collection of items as a plain array.
	 *
	 * @return array<TKey, TValue>
	 */
	public function toArray(): array {
	}

	/**
	 * Get the collection of items as a plain list.
	 *
	 * @return list<TValue>
	 */
	public function toList(): array {
	}
}
