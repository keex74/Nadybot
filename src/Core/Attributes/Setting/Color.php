<?php declare(strict_types=1);

namespace Nadybot\Core\Attributes\Setting;

use Attribute;
use Exception;

use Nadybot\Core\Attributes\DefineSetting;
use Nadybot\Core\Safe;
use Nadybot\Core\Types\SettingMode;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Color extends DefineSetting {
	/**
	 * @inheritDoc
	 *
	 * @param null|int|float|string|bool|list<mixed> $defaultValue
	 * @param array<string|int,int|string>           $options      An optional list of values that the setting can be, semi-colon delimited.
	 *                                                             Alternatively, use an associative array [label => value], where label is optional.
	 */
	public function __construct(
		public string $type='color',
		public ?string $name=null,
		public null|int|float|string|bool|array $defaultValue=null,
		public SettingMode $mode=SettingMode::Edit,
		public array $options=[],
		public string $accessLevel='mod',
		public ?string $help=null,
	) {
		$this->type = 'color';
	}

	public function getValue(): string {
		$value = parent::getValue();
		if (!is_string($value)) {
			throw new Exception("Type for {$this->name} must be string.");
		}
		if (count($matches = Safe::pregMatch('/^#?([0-9a-f]{6})$/i', $value))) {
			return $this->defaultValue = "<font color='#{$matches[1]}'>";
		}
		return $value;
	}
}
