<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use ValueError;

enum Channel: string {
	public function toNadybot(): string {
		return match ($this) {
			self::Org => 'guild',
			self::Tell => 'msg',
			self::Priv => 'priv',
			self::Discord => 'discord',
			self::IRC => 'irc',
		};
	}

	public static function fromNadybot(string $channel): self {
		return match (strtolower($channel)) {
			'guild' => self::Org,
			'msg' => self::Tell,
			'priv' => self::Priv,
			'discord' => self::Discord,
			'irc' => self::IRC,
			default => throw new ValueError("{$channel} is not a valid channel"),
		};
	}

	case Tell = 'tell';
	case Org = 'org';
	case Priv = 'priv';
	case Discord = 'discord';
	case IRC = 'irc';
}
