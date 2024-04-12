<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

use ValueError;

enum Faction: string {
	public function lower(): string {
		return strtolower($this->value);
	}

	public function inColor(?string $text=null): string {
		$text ??= $this->value;
		return "<{$this->lower()}>{$text}<end>";
	}

	public static function byName(string $name): self {
		return match (strtolower($name)) {
			'neutral','neut' => self::Neutral,
			'omni' => self::Omni,
			'clan' => self::Clan,
			default => throw new ValueError("Invalid faction '{$name}'"),
		};
	}

	public static function tryByName(string $name): ?self {
		try {
			return self::byName($name);
		} catch (\Throwable) {
			return null;
		}
	}

	case Neutral = 'Neutral';
	case Omni = 'Omni';
	case Clan = 'Clan';
	case Unknown = 'Unknown';
}
