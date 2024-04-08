<?php declare(strict_types=1);

namespace Nadybot\Core;

class CacheResult {
	/**
	 * @param bool    $success   Is the cache valid?
	 * @param bool    $usedCache Did this data come from the cache (true) or was it fetched (false)?
	 * @param bool    $oldCache  Is this cached information outdated?
	 *                           Usually, this should not be true, but if the cache
	 *                           is outdated and we were unable to renew the information
	 *                           from the URL, because of timeout or invalid content,
	 *                           then we consider outdated data to be better than none.
	 * @param int     $cacheAge  The age of the information in the cache in seconds
	 *                           0 if just refreshed
	 * @param ?string $data      The cached data as retrieved from the URL's body
	 */
	public function __construct(
		public bool $success=false,
		public bool $usedCache=false,
		public bool $oldCache=false,
		public int $cacheAge=0,
		public ?string $data=null,
	) {
	}
}
