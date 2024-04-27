<?php declare(strict_types=1);

namespace ParserGenerator;

class Parser {
	/**
	 * @param string $str
	 * @param int    $offset
	 *
	 * @return array{"line":int,"char":int}
	 */
	public static function getLineAndCharacterFromOffset($str, $offset) {
	}

	/**
	 * @param string $string
	 * @param string $nodeToParseName
	 *
	 * @return \ParserGenerator\SyntaxTreeNode\Root|false
	 */
	public function parse($string, $nodeToParseName='start') {
	}

	/** @return array{"index":int,"expected":list<mixed>} */
	public function getError() {
	}
}
