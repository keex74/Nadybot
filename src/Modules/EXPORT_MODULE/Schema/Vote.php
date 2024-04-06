<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Vote {
	/**
	 * @param ?Character $character The character voting for this answer
	 * @param ?int       $voteTime  When did the character vote for this?
	 */
	public function __construct(
		public ?Character $character=null,
		public ?int $voteTime=null,
	) {
	}
}
