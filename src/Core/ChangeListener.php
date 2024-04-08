<?php declare(strict_types=1);

namespace Nadybot\Core;

class ChangeListener {
	/** @param callable $callback */
	public function __construct(
		public $callback,
		public mixed $data,
	) {
	}
}
