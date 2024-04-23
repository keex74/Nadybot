<?php declare(strict_types=1);

namespace Nadybot\Core;

use function Safe\json_encode;

use EventSauce\ObjectHydrator\DoNotSerialize;
use Safe\Exceptions\JsonException;

trait StringableTrait {
	private static function __valueToString(mixed $value): string {
		if ($value === null) {
			return 'null';
		} elseif ($value instanceof \Stringable) {
			return (string)$value;
		} elseif ($value instanceof \UnitEnum) {
			return $value->name;
		} elseif ($value instanceof \Closure) {
			return '<Closure>';
		} elseif ($value instanceof \DateTimeInterface) {
			return $value->format("Y-m-d\TH:i:s");
		} elseif (is_array($value) && array_is_list($value)) {
			return '[' . implode(',', array_map(self::__valueToString(...), $value)) . ']';
		} elseif (is_array($value)) {
			$values = [];
			foreach ($value as $k => $v) {
				$values []= "{$k}=" . self::__valueToString($v);
			}
			return '{' . implode(',', $values) . '}';
		}
		$prefix = is_object($value) ? '<' . class_basename($value) . '>' : '';
		try {
			$value = json_encode(
				$value,
				\JSON_UNESCAPED_SLASHES|\JSON_UNESCAPED_UNICODE|\JSON_INVALID_UTF8_SUBSTITUTE
			);
		} catch (JsonException $e) {
			if (!is_object($value)) {
				throw $e;
			}
			$value = $prefix . '{}';
		}
		if (strlen($prefix) && $value === '{}') {
			$value = $prefix;
		} else {
			$value = $prefix . $value;
		}
		return $value;
	}

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
			$value = self::__valueToString($value);
			$values []= "{$key}={$value}";
		}
		$parts = explode('\\', static::class);
		$class = array_pop($parts);
		return "<{$class}>{" . implode(',', $values) . '}';
	}
}
