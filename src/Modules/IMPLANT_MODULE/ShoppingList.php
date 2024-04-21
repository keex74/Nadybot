<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

class ShoppingList {
	/**
	 * @param list<string> $implants
	 * @param list<string> $shinyClusters
	 * @param list<string> $brightClusters
	 * @param list<string> $fadedClusters
	 */
	public function __construct(
		public array $implants=[],
		public array $shinyClusters=[],
		public array $brightClusters=[],
		public array $fadedClusters=[],
	) {
	}
}
