<?php declare(strict_types=1);

namespace Nadybot\Core;

use Nadybot\Core\Routing\Source;

class PrivateChannelCommandReply implements CommandReply, MessageEmitter {
	public function __construct(
		private Nadybot $chatBot,
		private string $channel
	) {
	}

	public function getChannelName(): string {
		return Source::PRIV . "({$this->channel})";
	}

	public function reply($msg): void {
		$this->chatBot->sendPrivate($msg, false, $this->channel);
	}
}
