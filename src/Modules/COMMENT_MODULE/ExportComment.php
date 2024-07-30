<?php declare(strict_types=1);

namespace Nadybot\Modules\COMMENT_MODULE;

use Nadybot\Core\ExportCharacter;

class ExportComment {
	/**
	 * @param string           $comment         The actual comment about the character
	 * @param ExportCharacter  $targetCharacter The character who this comment is about
	 * @param ?ExportCharacter $createdBy       The character who created the comment
	 * @param ?int             $createdAt       When was the comment created?
	 * @param ?string          $category        If set, this specifies the category of the comment (ban, raid, admin, reputation, etc.). This requires that the category is also specified accordingly.
	 */
	public function __construct(
		public string $comment,
		public ExportCharacter $targetCharacter,
		public ?ExportCharacter $createdBy=null,
		public ?int $createdAt=null,
		public ?string $category=null,
	) {
	}
}
