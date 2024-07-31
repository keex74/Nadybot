<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Nadybot\Core\ExportCharacter;

class ExportPoll {
	/**
	 * @param string           $question      Which question is asked in the poll?
	 * @param ?ExportCharacter $author        The character who created the poll
	 * @param ?string          $minRankToVote If set, then only characters with this rank or higher are allowed to vote
	 * @param ?int             $startTime     When did/does the poll start?
	 * @param ?int             $endTime       When did/does the poll end?
	 * @param ?ExportAnswer[]  $answers
	 *
	 * @psalm-param ?list<ExportAnswer> $answers
	 */
	public function __construct(
		public string $question,
		public ?ExportCharacter $author=null,
		public ?string $minRankToVote=null,
		public ?int $startTime=null,
		public ?int $endTime=null,
		#[CastListToType(ExportAnswer::class)] public ?array $answers=null,
	) {
	}
}
