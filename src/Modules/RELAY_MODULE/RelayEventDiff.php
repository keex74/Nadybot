<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE;

class RelayEventDiff {
	/**
	 * @param string $event    Which event is this for?
	 * @param ?bool  $incoming Allow sending the event via this relay?
	 * @param ?bool  $outgoing Allow receiving the event via this relay?
	 */
	public function __construct(
		public string $event,
		public ?bool $incoming=null,
		public ?bool $outgoing=null,
	) {
	}
}
