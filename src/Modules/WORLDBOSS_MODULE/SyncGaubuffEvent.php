<?php declare(strict_types=1);

namespace Nadybot\Modules\WORLDBOSS_MODULE;

use Nadybot\Core\Attributes\{StrFuncIn, StrFuncOut};
use Nadybot\Core\{Faction, SyncEvent, Util};

class SyncGaubuffEvent extends SyncEvent {
	public const EVENT_MASK = 'sync(gaubuff)';

	/**
	 * @param int     $expires UNIX timestamp when the buff expires
	 * @param Faction $faction For which faction: neutral, clan or omni
	 * @param string  $sender  Name of the person reporting the gauntlet buff
	 */
	public function __construct(
		public int $expires,
		#[
			StrFuncIn('ucfirst'),
			StrFuncOut([Util::class, 'enumToValue'], 'strtolower')
		] public Faction $faction,
		public string $sender,
		?string $sourceBot=null,
		?int $sourceDimension=null,
		?bool $forceSync=null,
	) {
		$this->type = self::EVENT_MASK;
		parent::__construct($sourceBot, $sourceDimension, $forceSync);
	}
}
