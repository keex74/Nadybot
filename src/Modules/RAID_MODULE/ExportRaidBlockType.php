<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE;

enum ExportRaidBlockType: string {
	case Points = 'points';
	case Join = 'join';
	case Bid = 'bid';
}
