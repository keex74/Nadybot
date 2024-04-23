<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE\Layer;

class HighwayPublic extends Highway {
	/** @param list<string> $rooms */
	public function __construct(array $rooms) {
		$this->rooms = $rooms;
	}
}
