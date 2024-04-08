<?php declare(strict_types=1);

namespace Nadybot\Modules\NEWS_MODULE;

use Nadybot\Core\DBRow;

class NewNews extends DBRow {
	/**
	 * @param ?string $news    Text of these news
	 * @param ?int    $time    Unix timestamp when this was created
	 * @param ?string $name    Name of the character who created the entry
	 * @param ?bool   $sticky  Set to true if this is pinned above all unpinned news
	 * @param ?bool   $deleted Set to true if this is actually deleted
	 */
	public function __construct(
		public ?string $news,
		public ?int $time=null,
		public ?string $name=null,
		public ?bool $sticky=null,
		public ?bool $deleted=null,
	) {
	}
}
