<?php declare(strict_types=1);

namespace Nadybot\Modules\COMMENT_MODULE;

class FormattedComments {
	/**
	 * @param string $blob        The formatted text as blob
	 * @param int    $numChars    How many different characters have comments in here?
	 * @param int    $numMains    How many different players have comments in here?
	 * @param int    $numComments How many comments are there in total?
	 */
	public function __construct(
		public string $blob,
		public int $numChars=0,
		public int $numMains=0,
		public int $numComments=0,
	) {
	}
}
