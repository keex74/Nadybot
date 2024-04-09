<?php declare(strict_types=1);

namespace Nadybot\Modules\DEV_MODULE;

use Nadybot\Core\Attributes\DB\Table;
use Nadybot\Core\DBTable;

#[Table(name: 'silence_cmd')]
class SilenceCmd extends DBTable {
	public function __construct(
		public string $cmd,
		public string $channel,
	) {
	}
}
