<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class Answer {
	/**
	 * @param string  $answer The choice for this answer
	 * @param ?Vote[] $votes
	 */
	public function __construct(
		public string $answer,
		#[CastListToType(Vote::class)] public ?array $votes=null,
	) {
	}
}
