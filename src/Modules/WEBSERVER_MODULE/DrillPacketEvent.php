<?php declare(strict_types=1);

namespace Nadybot\Modules\WEBSERVER_MODULE;

use Amp\Websocket\Client\WebsocketConnection;
use Nadybot\Core\Safe;
use Nadybot\Modules\WEBSERVER_MODULE\Drill\Packet;

class DrillPacketEvent extends DrillEvent {
	public const EVENT_MASK = 'drill(*)';

	public function __construct(
		WebsocketConnection $client,
		public Packet\Base $packet,
	) {
		parent::__construct(client: $client);
		$kebabCase = Safe::pregReplace(
			'/([a-z])([A-Z])/',
			'$1-$2',
			class_basename($packet)
		);
		$this->type = 'drill(' . strtolower($kebabCase) . ')';
	}
}
