<?php declare(strict_types=1);

namespace Nadybot\Core\Attributes\DB;

enum Shared: int {
	case Yes = 1;
	case No = 0;
	case Both = 2;
}
