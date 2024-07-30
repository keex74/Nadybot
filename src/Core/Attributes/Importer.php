<?php declare(strict_types=1);

namespace Nadybot\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Importer {
	/** @psalm-param class-string $class */
	public function __construct(
		public string $key,
		public string $class,
	) {
	}
}
