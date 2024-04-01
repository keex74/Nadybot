<?php declare(strict_types=1);

namespace Nadybot\Modules\BASIC_CHAT_MODULE;

use Safe\DateTimeImmutable;

class CallerBackup {
	public DateTimeImmutable $time;

	/**
	 * Names of all callers
	 *
	 * @var array<string,CallerList>
	 */
	public array $callers = [];

	public string $changer;

	public string $command;

	/** @param array<string,CallerList> $callers */
	public function __construct(string $changer, string $command, array $callers) {
		$this->time = new DateTimeImmutable();
		$this->changer = $changer;
		$this->command = $command;
		$this->callers = $callers;
	}
}
