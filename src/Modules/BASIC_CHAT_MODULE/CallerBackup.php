<?php declare(strict_types=1);

namespace Nadybot\Modules\BASIC_CHAT_MODULE;

use Safe\DateTimeImmutable;

class CallerBackup {
	public DateTimeImmutable $time;

	/** @param array<string,CallerList> $callers Names of all callers */
	public function __construct(
		public string $changer,
		public string $command,
		public array $callers=[],
	) {
		$this->time = new DateTimeImmutable();
	}
}
