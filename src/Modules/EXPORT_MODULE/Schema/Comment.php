<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Comment {
	/**
	 * @param string     $comment         The actual comment about the character
	 * @param Character  $targetCharacter The character who this comment is about
	 * @param ?Character $createdBy       The character who created the comment
	 * @param ?int       $createdAt       When was the comment created?
	 * @param ?string    $category        If set, this specifies the category of the comment (ban, raid, admin, reputation, etc.). This requires that the category is also specified accordingly.
	 */
	public function __construct(
		public string $comment,
		public Character $targetCharacter,
		public ?Character $createdBy=null,
		public ?int $createdAt=null,
		public ?string $category=null,
	) {
	}
}
