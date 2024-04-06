<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Note {
	/**
	 * @param Character  $owner        The character who is considered the owner of this note (usually the main)
	 * @param string     $text         The text of the note
	 * @param ?Character $author       The character who posted the note
	 * @param ?int       $creationTime Timestamp of when the note was created
	 * @param ?string    $remind       If set, remind either all of the owner's alts or only the author about the note
	 */
	public function __construct(
		public Character $owner,
		public string $text,
		public ?Character $author=null,
		public ?int $creationTime=null,
		public ?string $remind=null,
	) {
	}
}
