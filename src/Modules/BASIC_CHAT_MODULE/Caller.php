<?php declare(strict_types=1);

namespace Nadybot\Modules\BASIC_CHAT_MODULE;

use Safe\DateTimeImmutable;

class Caller {
	/** What was the caller added */
	public DateTimeImmutable $addedWhen;

	/**
	 * @param string $name    Name of the caller
	 * @param string $addedBy Who added the caller
	 */
	public function __construct(
		public string $name,
		public string $addedBy,
	) {
		$this->addedWhen = new DateTimeImmutable();
	}
}
