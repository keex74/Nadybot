<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class ExportAnswer {
	/**
	 * @param string        $answer The choice for this answer
	 * @param ?ExportVote[] $votes
	 *
	 * @psalm-param ?list<ExportVote> $votes
	 */
	public function __construct(
		public string $answer,
		#[CastListToType(ExportVote::class)] public ?array $votes=null,
	) {
	}
}
