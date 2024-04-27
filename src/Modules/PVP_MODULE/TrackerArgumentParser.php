<?php declare(strict_types=1);

namespace Nadybot\Modules\PVP_MODULE;

use function Safe\json_decode;

use Exception;
use ParserGenerator\Parser;

use ParserGenerator\SyntaxTreeNode\{Branch, Root};

class TrackerArgumentParser {
	protected Parser $parser;

	public function getParser(): Parser {
		if (!isset($this->parser)) {
			$this->parser = new Parser(
				$this->getExpressionDefinition(),
				['ignoreWhitespaces' => true]
			);
		}
		return $this->parser;
	}

	public function getExpressionDefinition(): string {
		return '
			start   :=> argumentList
			        :=> argumentList eventList.
			eventList :=> event
			          :=> event eventList.
			event :=> /[a-zA-Z-*]+/.
			argumentName :=> /[a-zA-Z_0-9-]+/.
			argumentList :=> argument
			             :=> argument argumentList.
			argument :=> key "=" value.
			key :=> argumentName.
			value:int => -inf..inf
			     :bool => ("true"|"false")
			     :string => string
			     :simpleString => /[a-zA-Z_0-9!-]+/.
		';
	}

	/** @throws TrackerArgumentParserException */
	public function parse(string $input): TrackerConfig {
		$parser = $this->getParser();

		/** @var Root|false */
		$expr = $parser->parse($input);
		if ($expr === false) {
			$error = $parser->getError();

			/** @var array{"char":int} */
			$posData = $this->parser::getLineAndCharacterFromOffset($input, $error['index']);

			$expected = implode('<end> or <highlight>', $this->parser->generalizeErrors($error['expected']));
			$foundLength = 20;
			$found = substr($input, $error['index']);
			if (strlen($found) > $foundLength) {
				$found = substr($found, 0, $foundLength) . '...';
			}

			$char = substr($input, $posData['char']-1, 1);
			if ($found !== '') {
				$found = ", found: <highlight>\"{$found}\"<end>";
			}
			throw new TrackerArgumentParserException(
				substr($input, 0, $posData['char']-1).
				'<red>' . (strlen($char) ? $char : '_') . '<end>'.
				substr($input, $posData['char']) . "\n".
				"expected: <highlight>{$expected}<end>{$found}."
			);
		}
		$config = new TrackerConfig();
		$modifiers = $expr->findAll('argument');
		foreach ($modifiers as $modifier) {
			$config->arguments []= $this->parseArgument($modifier);
		}
		$events = $expr->findAll('event');
		foreach ($events as $event) {
			$config->events []= $this->parseEvent($event);
		}
		return $config;
	}

	protected function parseArgument(Branch $argument): TrackerArgument {
		$value = $argument->findFirst('value');
		if (!isset($value)) {
			throw new Exception('Invalid tracker argument structure');
		}
		if ($value->getDetailType() === 'string') {
			$rValue = json_decode($value->toString());
		} else {
			$rValue = $value->toString();
		}
		$argumentName = $argument->findFirst('key')?->toString();
		if (!isset($argumentName)) {
			throw new Exception('Invalid tracker argument structure');
		}
		return new TrackerArgument(
			name: $argumentName,
			value: $rValue,
		);
	}

	protected function parseEvent(Branch $event): string {
		return $event->toString();
	}
}
