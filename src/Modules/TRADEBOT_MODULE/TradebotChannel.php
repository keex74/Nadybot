<?php declare(strict_types=1);

namespace Nadybot\Modules\TRADEBOT_MODULE;

use Nadybot\Core\Types\MessageEmitter;

class TradebotChannel implements MessageEmitter {
	public function __construct(private string $bot) {
	}

	public function getChannelName(): string {
		return "tradebot({$this->bot})";
	}
}
