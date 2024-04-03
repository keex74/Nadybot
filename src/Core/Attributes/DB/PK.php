<?php declare(strict_types=1);

namespace Nadybot\Core\Attributes\DB;

use Attribute;

/**
 * This property is the primary key of the database table,
 * or part of a set of primary keys
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class PK {
	public function __construct() {
	}
}
