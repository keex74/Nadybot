<?php declare(strict_types=1);

namespace Nadybot\Core;

use Nadybot\Core\Routing\Source;

class PrivateMessageCommandReply implements CommandReply, MessageEmitter {
	public function __construct(
		private Nadybot $chatBot,
		private string $sender,
		private ?int $worker=null
	) {
	}

	public function getChannelName(): string {
		return Source::TELL . "({$this->sender})";
	}

	public function reply($msg): void {
		if (isset($this->worker)) {
			$this->chatBot->sendMassTell($msg, $this->sender, null, true, $this->worker);
		} else {
			$this->chatBot->sendTell($msg, $this->sender);
		}
	}
}
