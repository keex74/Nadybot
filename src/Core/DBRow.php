<?php declare(strict_types=1);

namespace Nadybot\Core;

use Nadybot\Core\Attributes\DB\Table;
use ReflectionClass;
use Stringable;
use ValueError;

class DBRow implements Stringable {
	use StringableTrait;

	public function __get(string $value): mixed {
		$backtrace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 2);
		$trace = $backtrace[1];
		$trace2 = $backtrace[0];
		$logger = new LoggerWrapper('Core/DB');
		Registry::injectDependencies($logger);
		$logger->warning("Tried to get value '{$value}' from row that doesn't exist: " . var_export($this, true));
		$class = '';
		if (isset($trace['class'])) {
			$class = $trace['class'] . '::';
		}
		$logger->warning('Called by {class}{function}() in {file} line {line}', [
			'class' => $class,
			'function' => $trace['function'],
			'file' => $trace2['file'] ?? 'unknown',
			'line' => $trace2['line'] ?? 'unknown',
		]);
		return null;
	}

	/**
	 * Get the name of the table represented by this class
	 *
	 * @throws ValueError if there is no table defined
	 */
	public static function getTable(?string $as=null): string {
		$refClass = new ReflectionClass(static::class);
		$tableDefs = $refClass->getAttributes(Table::class);
		if (!count($tableDefs)) {
			throw new ValueError('The class ' . static::class . " doesn't have a table defined.");
		}
		$tableName = $tableDefs[0]->newInstance()->getName();
		if (isset($as)) {
			$tableName .= " AS {$as}";
		}
		return $tableName;
	}

	public static function tryGetTable(?string $as=null): ?string {
		try {
			return self::getTable($as);
		} catch (\Throwable) {
		}
		return null;
	}
}
