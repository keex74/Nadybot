<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

use Nadybot\Core\ExportCharacter;

class ExportVote {
	/**
	 * @param ?ExportCharacter $character The character voting for this answer
	 * @param ?int             $voteTime  When did the character vote for this?
	 */
	public function __construct(
		public ?ExportCharacter $character=null,
		public ?int $voteTime=null,
	) {
	}
}
