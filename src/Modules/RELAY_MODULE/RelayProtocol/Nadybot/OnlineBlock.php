<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE\RelayProtocol\Nadybot;

use Nadybot\Core\Routing\Source;

class OnlineBlock {
	/** @var list<Source> */
	public array $path = [];

	/** @var list<RelayCharacter> */
	public array $users = [];
}
