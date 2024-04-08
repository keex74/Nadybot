<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\DBRow;

class HelpTopic extends DBRow {
	public function __construct(
		public string $admin_list,
		public string $module,
		public string $name,
		public string $description,
		public ?int $sort=null,
		public ?string $file=null,
	) {
	}
}
