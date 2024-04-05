<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use InvalidArgumentException;

class Character {
	/**
	 * An object representing an AO character
	 *
	 * @param ?string $name The name of this character
	 * @param ?int    $id   The user id of  this character
	 */
	public function __construct(
		public ?string $name=null,
		public ?int $id=null,
	) {
		if (!isset($name) && !isset($id)) {
			throw new InvalidArgumentException(__CLASS__ . ' must have at least one of $id and $name');
		}
	}
}
