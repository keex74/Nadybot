<?php declare(strict_types=1);

namespace Nadybot\Core;

use Monolog\Processor\ProcessorInterface;
use Monolog\Utils;

/**
 * Processes a record's message according to PSR-3 rules
 *
 * It replaces {foo} with the value from $context['foo']
 *
 * @author Nadyita <nadyita@hodorraid.org>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class PsrLogMessageProcessor implements ProcessorInterface {
	public const SIMPLE_DATE = "Y-m-d\TH:i:s";

	/** @var string|null */
	private $dateFormat;

	/** @var bool */
	private $removeUsedContextFields;

	/**
	 * @param string|null $dateFormat              The format of the timestamp: one supported by DateTime::format
	 * @param bool        $removeUsedContextFields If set to true the fields interpolated into message gets unset
	 */
	public function __construct(?string $dateFormat=null, bool $removeUsedContextFields=false) {
		$this->dateFormat = $dateFormat;
		$this->removeUsedContextFields = $removeUsedContextFields;
	}

	/** {@inheritDoc} */
	public function __invoke(array $record): array {
		if (!str_contains($record['message'], '{')) {
			return $record;
		}

		$replacements = [];
		foreach ($record['context'] as $key => $val) {
			$placeholder = '{' . $key . '}';
			if (!str_contains($record['message'], $placeholder)) {
				continue;
			}
			$replacements[$placeholder] = $this->toReplacement($val);

			if ($this->removeUsedContextFields) {
				unset($record['context'][$key]);
			}
		}

		$record['message'] = strtr($record['message'], $replacements);

		return $record;
	}

	private function toReplacement(mixed $val): mixed {
		if (is_null($val)) {
			return '<null>';
		} elseif (is_scalar($val)) {
			return $val;
		} elseif (is_object($val) && method_exists($val, '__toString')) {
			return (string)$val;
		} elseif ($val instanceof \DateTimeInterface) {
			if (!isset($this->dateFormat) && $val instanceof \Monolog\DateTimeImmutable) {
				// handle monolog dates using __toString if no specific dateFormat was asked for
				// so that it follows the useMicroseconds flag
				return (string)$val;
			}
			return $val->format($this->dateFormat ?? static::SIMPLE_DATE);
		} elseif ($val instanceof \UnitEnum) {
			return $val instanceof \BackedEnum ? $val->value : $val->name;
		} elseif (is_object($val)) {
			if ($val instanceof Loggable) {
				return $val->toLog();
			}
			return '[object ' . Utils::getClass($val) . ']';
		} elseif (is_array($val)) {
			if (array_is_list($val)) {
				return '[' . implode(',', array_map($this->toReplacement(...), $val)) . ']';
			}
			return 'array' . Utils::jsonEncode($val, null, true);
		}
		return '[' . gettype($val) . ']';
	}
}
