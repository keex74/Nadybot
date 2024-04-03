<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

use Nadybot\Core\Attributes\DB\{Shared, Table};
use Nadybot\Core\DBRow;

#[Table(name: 'aodb', shared: Shared::Yes)]
class AODBEntry extends DBRow {
	use AODBTrait;
}
