<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Link {
	/**
	 * @param string     $url          The URL of this link
	 * @param ?Character $createdBy    The character who submitted the URL
	 * @param ?string    $description  A description what this URL is about
	 * @param ?int       $creationTime Timestamp of when the links was submitted
	 */
	public function __construct(
		public string $url,
		public ?Character $createdBy=null,
		public ?string $description=null,
		public ?int $creationTime=null,
	) {
	}
}
