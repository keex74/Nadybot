<?php declare(strict_types=1);

namespace Nadybot\Modules\BASIC_CHAT_MODULE;

use Safe\DateTimeImmutable;

class Caller {
	/** Name of the caller */
	public string $name;

	/** Who added the caller */
	public string $addedBy;

	/** What was the caller added */
	public DateTimeImmutable $addedWhen;

	public function __construct(?string $name=null, ?string $addedBy=null) {
		$this->addedWhen = new DateTimeImmutable();
		if (isset($name)) {
			$this->name = $name;
		}
		if (isset($addedBy)) {
			$this->addedBy = $addedBy;
		}
	}
}
