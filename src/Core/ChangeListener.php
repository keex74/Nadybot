<?php declare(strict_types=1);

namespace Nadybot\Core;

use Closure;

class ChangeListener {
	public function __construct(
		public Closure $callback,
		public mixed $data,
	) {
	}
}
