<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE;

class TrackerLogonEvent extends TrackerEvent {
	public const EVENT_MASK = 'tracker(logon)';

	public function __construct(
		string $player,
		int $uid,
	) {
		parent::__construct(player: $player, uid: $uid);
		$this->type = self::EVENT_MASK;
	}
}
