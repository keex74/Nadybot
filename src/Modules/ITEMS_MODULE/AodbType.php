<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE;

enum AodbType: string {
	case Misc = 'Misc';
	case Armor = 'Armor';
	case Implant = 'Implant';
	case Crystal = 'Crystal';
	case Weapon = 'Weapon';
	case Disc = 'Disc';
	case Spirir = 'Spirit';
	case Tainted = 'Tainted';
}
