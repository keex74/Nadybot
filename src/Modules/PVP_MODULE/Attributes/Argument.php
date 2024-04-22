<?php declare(strict_types=1);

namespace Nadybot\Modules\PVP_MODULE\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Argument {
	/**
	 * @param list<string> $names
	 * @param list<string> $examples
	 *
	 * @psalm-param non-empty-array<string> $names
	 */
	public function __construct(
		public array $names,
		public string $description,
		public string $type,
		public array $examples=[],
	) {
	}
}
