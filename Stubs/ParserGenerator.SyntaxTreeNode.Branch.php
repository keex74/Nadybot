<?php declare(strict_types=1);

namespace ParserGenerator\SyntaxTreeNode;

class Branch extends \ParserGenerator\SyntaxTreeNode\Base {
	/** @return string */
	public function __toString() {
	}

	/** @return string */
	public function getType() {
	}

	/**
	 * @param string $newValue
	 *
	 * @return $this
	 */
	public function setType($newValue) {
	}

	/**
	 * @param int $mode
	 *
	 * @psalm-param int-mask-of<\ParserGenerator\SyntaxTreeNode\Base::TO_STRING_*> $mode
	 *
	 * @return string
	 */
	public function toString($mode=Base::TO_STRING_NO_WHITESPACES) {
	}

	/** @return list<Leaf> */
	public function getLeafs() {
	}

	/**
	 * @param string $type
	 * @param bool   $nest
	 * @param bool   $childrenFirst
	 *
	 * @return list<Branch>
	 */
	public function findAll($type, $nest=false, $childrenFirst=false) {
	}

	/**
	 * @param string $type
	 * @param bool   $startingOnly
	 *
	 * @return ?Branch
	 */
	public function findFirst($type, $startingOnly=false) {
	}

	/**
	 * @param Branch $anotherNode
	 * @param int    $compareOptions
	 *
	 * @psalm-param int-mask-of<Base::COMPARE_*>
	 *
	 * @return bool
	 */
	public function compare($anotherNode, $compareOptions=Base::COMPARE_DEFAULT) {
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

	/**
	 * @param int $index
	 *
	 * @return ?self
	 */
	public function getSubnode($index) {
	}

	/**
	 * @param string $type
	 *
	 * @return bool
	 */
	protected function is($type) {
	}
}
