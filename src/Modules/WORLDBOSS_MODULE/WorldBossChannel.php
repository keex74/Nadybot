<?php declare(strict_types=1);

namespace Nadybot\Modules\WORLDBOSS_MODULE;

use Nadybot\Core\Types\MessageEmitter;

class WorldBossChannel implements MessageEmitter {
	public function __construct(private string $bossName) {
	}

	public function getChannelName(): string {
		return "spawn({$this->bossName})";
	}
}
