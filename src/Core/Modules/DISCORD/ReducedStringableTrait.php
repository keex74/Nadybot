<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use EventSauce\ObjectHydrator\DoNotSerialize;
use Nadybot\Core\StringableTrait;
use ReflectionException;

trait ReducedStringableTrait {
	use StringableTrait;

	#[DoNotSerialize]
	public function __toString(): string {
		$values = [];
		$refClass = new \ReflectionClass($this);
		$props = get_object_vars($this);
		foreach ($props as $key => $value) {
			$refProp = $refClass->getProperty($key);
			if ($refProp->isInitialized($this) === false) {
				continue;
			}
			if ($refProp->hasDefaultValue()) {
				$defaultValue = $refProp->getDefaultValue();
				if ($defaultValue === $value) {
					continue;
				}
			} elseif ($refProp->isPromoted() && null !== ($refFunc = $refClass->getConstructor())) {
				$params = $refFunc->getParameters();
				$isDefault = false;
				foreach ($params as $param) {
					if ($param->name !== $key) {
						continue;
					}
					try {
						$isDefault = $param->getDefaultValue() === $value;
						break;
					} catch (ReflectionException) {
					}
				}
				if ($isDefault) {
					continue;
				}
			}
			if ($value === null) {
				continue;
			}
			$value = self::__valueToString($value);
			$values []= "{$key}={$value}";
		}
		$parts = explode('\\', static::class);
		$class = array_pop($parts);
		return "<{$class}>{" . implode(',', $values) . '}';
	}
}
