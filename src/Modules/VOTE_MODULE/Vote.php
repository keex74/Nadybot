<?php declare(strict_types=1);

namespace Nadybot\Modules\VOTE_MODULE;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBRow;

#[Table(name: 'votes')]
class Vote extends DBRow {
	public function __construct(
		public int $poll_id,
		public string $author,
		public ?string $answer=null,
		public ?int $time=null,
	) {
	}
}
