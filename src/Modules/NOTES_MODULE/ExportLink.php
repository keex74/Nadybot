<?php declare(strict_types=1);

namespace Nadybot\Modules\NOTES_MODULE;

use Nadybot\Core\ExportCharacter;

class ExportLink {
	/**
	 * @param string           $url          The URL of this link
	 * @param ?ExportCharacter $createdBy    The character who submitted the URL
	 * @param ?string          $description  A description what this URL is about
	 * @param ?int             $creationTime Timestamp of when the links was submitted
	 */
	public function __construct(
		public string $url,
		public ?ExportCharacter $createdBy=null,
		public ?string $description=null,
		public ?int $creationTime=null,
	) {
	}
}
