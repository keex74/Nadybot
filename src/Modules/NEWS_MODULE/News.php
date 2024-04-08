<?php declare(strict_types=1);

namespace Nadybot\Modules\NEWS_MODULE;

use Nadybot\Core\{Attributes as NCA, DBRow, Util};

#[NCA\DB\Table(name: 'news', shared: NCA\DB\Shared::Yes)]
class News extends DBRow {
	public string $uuid;

	/**
	 * @param int    $time    Unix timestamp when this was created
	 * @param string $name    Name of the character who created the entry
	 * @param string $news    Text of these news
	 * @param bool   $sticky  Set to true if this is pinned above all unpinned news
	 * @param bool   $deleted Set to true if this is actually deleted
	 * @param string $uuid    The UUID of this news entry
	 * @param ?int   $id      The internal ID of this news entry
	 */
	public function __construct(
		public int $time,
		public string $name,
		public string $news,
		public bool $sticky,
		public bool $deleted,
		?string $uuid=null,
		#[NCA\DB\AutoInc] public ?int $id=null,
	) {
		$this->uuid = $uuid ?? Util::createUUID();
	}
}
