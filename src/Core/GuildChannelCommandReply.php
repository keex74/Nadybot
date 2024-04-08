<?php declare(strict_types=1);

namespace Nadybot\Core;

use Nadybot\Core\Routing\Source;

class GuildChannelCommandReply implements CommandReply, MessageEmitter {
	public function __construct(
		private Nadybot $chatBot
	) {
	}

	public function getChannelName(): string {
		return Source::ORG;
	}

	/** @inheritDoc */
	public function reply($msg): void {
		$this->chatBot->sendGuild($msg);
	}
}
