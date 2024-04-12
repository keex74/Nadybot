<?php declare(strict_types=1);

namespace Nadybot\Modules\WEBSERVER_MODULE;

use Amp\Websocket\Client\WebsocketConnection;
use Nadybot\Core\Events\Event;

class DrillEvent extends Event {
	public const EVENT_MASK = 'drill(*)';

	public function __construct(
		public WebsocketConnection $client,
	) {
		$this->type = self::EVENT_MASK;
	}
}
