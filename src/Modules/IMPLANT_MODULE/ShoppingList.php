<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

class ShoppingList {
	/**
	 * @param string[] $implants
	 * @param string[] $shinyClusters
	 * @param string[] $brightClusters
	 * @param string[] $fadedClusters
	 */
	public function __construct(
		public array $implants=[],
		public array $shinyClusters=[],
		public array $brightClusters=[],
		public array $fadedClusters=[],
	) {
	}
}
