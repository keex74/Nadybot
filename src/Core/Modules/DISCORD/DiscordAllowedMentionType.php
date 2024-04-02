<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

enum DiscordAllowedMentionType: string {
	case Roles = 'roles';
	case Users = 'users';
	case Everyone = 'everyone';
	case Here = 'here';
}
