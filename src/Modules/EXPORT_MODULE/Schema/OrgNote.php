<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class OrgNote {
	/**
	 * @param string     $text         The text of the note
	 * @param ?Character $author       The character who posted the note
	 * @param ?int       $creationTime Timestamp of when the note was created
	 * @param ?string    $uuid         The unique itentifier for this org note
	 */
	public function __construct(
		public string $text,
		public ?Character $author=null,
		public ?int $creationTime=null,
		#[
			StrFormat('^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$')
		] public ?string $uuid=null,
	) {
	}
}
