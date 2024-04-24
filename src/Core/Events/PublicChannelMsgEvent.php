<?php declare(strict_types=1);

namespace Nadybot\Core\Events;

abstract class PublicChannelMsgEvent extends AOChatEvent {
	/**
	 * @param string  $channel The name of the public channel via which the message was sent
	 * @param string  $message The message itself
	 * @param ?string $worker  If set, this is the id of the worker via which the message was received
	 * @param string  $sender  The name of the sender of the message
	 */
	public function __construct(
		public string $channel,
		public string $message,
		public ?string $worker=null,
		public ?string $sender=null,
	) {
	}
}