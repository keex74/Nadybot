<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RelayProp {
	public function __construct(public string $name) {
	}
}
