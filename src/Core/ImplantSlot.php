<?php declare(strict_types=1);

namespace Nadybot\Core;

use ValueError;

enum ImplantSlot: int {
	public static function byName(string $name): self {
		return match (strtolower($name)) {
			'eye','eyes','ocular' => self::Eye,
			'head','brain' => self::Head,
			'ear' => self::Ear,
			'right arm','rarm' => self::RightArm,
			'body','chest' => self::Chest,
			'left arm','larm' => self::LeftArm,
			'right wrist','rwrist' => self::RightWrist,
			'waist' => self::Waist,
			'left wrist','lwrist' => self::LeftWrist,
			'right hand','rhand' => self::RightHand,
			'legs','leg','thigh' => self::Leg,
			'left hand','lhand' => self::LeftHand,
			'foot','feet' => self::Feet,
			default => throw new ValueError("Unknown implant slot name '{$name}'"),
		};
	}

	public function designSlotName(): string {
		return match ($this) {
			self::Eye => 'eye',
			self::Head => 'head',
			self::Ear => 'ear',
			self::RightArm => 'rarm',
			self::Chest => 'chest',
			self::LeftArm => 'larm',
			self::RightWrist => 'rwrist',
			self::Waist => 'waist',
			self::LeftWrist => 'lwrist',
			self::RightHand => 'rhand',
			self::Leg => 'legs',
			self::LeftHand => 'lhand',
			self::Feet => 'feet',
		};
	}

	public function longName(): string {
		return match ($this) {
			self::Eye => 'Ocular',
			self::Head => 'Brain',
			self::Ear => 'Ear',
			self::RightArm => 'Right Arm',
			self::Chest => 'Chest',
			self::LeftArm => 'Left Arm',
			self::RightWrist => 'Right Wrist',
			self::Waist => 'Waist',
			self::LeftWrist => 'Left Wrist',
			self::RightHand => 'Right Hand',
			self::Leg => 'Thigh',
			self::LeftHand => 'Left Hand',
			self::Feet => 'Feet',
		};
	}

	case Eye = 2;
	case Head = 4;
	case Ear = 8;
	case RightArm = 16;
	case Chest = 32;
	case LeftArm = 64;
	case RightWrist = 128;
	case Waist = 256;
	case LeftWrist = 512;
	case RightHand = 1_024;
	case Leg = 2_048;
	case LeftHand = 4_096;
	case Feet = 8_192;
}
