<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class Poll {
	/**
	 * @param string     $question      Which question is asked in the poll?
	 * @param ?Character $author        The character who created the poll
	 * @param ?string    $minRankToVote If set, then only characters with this rank or higher are allowed to vote
	 * @param ?int       $startTime     When did/does the poll start?
	 * @param ?int       $endTime       When did/does the poll end?
	 * @param ?Answer[]  $answers
	 */
	public function __construct(
		public string $question,
		public ?Character $author=null,
		public ?string $minRankToVote=null,
		public ?int $startTime=null,
		public ?int $endTime=null,
		#[CastListToType(Answer::class)] public ?array $answers=null,
	) {
	}
}
