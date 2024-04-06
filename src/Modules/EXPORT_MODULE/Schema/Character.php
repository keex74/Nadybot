<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use InvalidArgumentException;

class Character {
	/**
	 * @param ?string $name The name of this character
	 * @param ?int    $id   The user Id of this character
	 */
	public function __construct(
		#[StrLength(min: 4, max: 12)] public ?string $name=null,
		#[Min(1)] public ?int $id=null,
	) {
		if (!isset($name) && !isset($id)) {
			throw new InvalidArgumentException('A character consists of at least a UID or a name');
		}
	}
}
