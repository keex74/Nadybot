<?php declare(strict_types=1);

namespace ParserGenerator\SyntaxTreeNode;

class Branch extends \ParserGenerator\SyntaxTreeNode\Base {
	/** @return string */
	public function __toString() {
	}

	/** @return string */
	public function getType() {
	}

	/** @return $this */
	public function setType($newValue) {
	}

	/** @return string */
	public function toString($mode = \ParserGenerator\SyntaxTreeNode\Base::TO_STRING_NO_WHITESPACES) {
	}

	/** @return list<Leaf> */
	public function getLeafs() {
	}

	/** @return list<Branch> */
	public function findAll($type, $nest = false, $childrenFirst = false) {
	}

	/** @return ?Branch */
	public function findFirst($type, $startingOnly = false) {
	}

	/** @return bool */
	public function compare($anotherNode, $compareOptions = \ParserGenerator\SyntaxTreeNode\Base::COMPARE_DEFAULT) {
	}

	/** @return Leaf */
	public function getLeftLeaf() {
	}

	/** @return Leaf */
	public function getRightLeaf() {
	}

	/** @return true */
	public function isBranch() {
	}

	/** @return bool */
	protected function is($type) {
	}
}
