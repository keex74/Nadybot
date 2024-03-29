<?php declare(strict_types=1);

namespace Nadybot\Core;

enum SettingMode: string {
	case Edit = 'edit';
	case NoEdit = 'noedit';
}
