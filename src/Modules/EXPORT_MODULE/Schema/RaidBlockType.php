<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

enum RaidBlockType: string {
	case Points = 'points';
	case Join = 'join';
	case Bid = 'bid';
}
