<?php declare(strict_types=1);

namespace Nadybot\Modules\BASIC_CHAT_MODULE;

class TopicSetEvent extends TopicEvent {
	public const EVENT_MASK = "topic(set)";

	/**
	 * @param string $player The names of the sender
	 * @param string $topic  The topic that was set
	 */
	public function __construct(
		public string $player,
		public string $topic,
	) {
		$this->type = self::EVENT_MASK;
	}
}
