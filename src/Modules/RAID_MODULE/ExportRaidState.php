<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

class ExportRaidState {
	/**
	 * @param ?int    $time                 When did the raid state change to this?
	 * @param ?string $raidDescription      What was raided?
	 * @param ?bool   $raidLocked           Is/was the raid locked and only raid leaders were allowed to add raiders?
	 * @param ?int    $raidAnnounceInterval How many seconds between announcing the raid?
	 * @param ?int    $raidSecondsPerPoint  If this is set, then raiders are automatically
	 *                                      awarded raid points and this is the interval between
	 *                                      receiving 1 point. If this is null, then the ticker
	 *                                      is explicitly set off.
	 */
	public function __construct(
		public ?int $time=null,
		public ?string $raidDescription=null,
		public ?bool $raidLocked=null,
		public ?int $raidAnnounceInterval=null,
		public ?int $raidSecondsPerPoint=null,
	) {
	}
}
