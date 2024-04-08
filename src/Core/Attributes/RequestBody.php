<?php declare(strict_types=1);

namespace Nadybot\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestBody {
	/** @param class-string|string $class */
	public function __construct(
		public string $class,
		public string $desc,
		public bool $required
	) {
	}
}
