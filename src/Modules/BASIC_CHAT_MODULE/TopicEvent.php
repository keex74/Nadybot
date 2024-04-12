<?php declare(strict_types=1);

namespace Nadybot\Modules\BASIC_CHAT_MODULE;

use Nadybot\Core\Events\Event;

abstract class TopicEvent extends Event {
	public const EVENT_MASK = 'topic(*)';

	/** The names of the sender */
	public string $player;

	/** The topic that was set or unset if cleared */
	public string $topic;
}
