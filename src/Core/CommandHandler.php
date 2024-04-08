<?php declare(strict_types=1);

namespace Nadybot\Core;

class CommandHandler {
	/** @var string[] */
	public array $files;

	public function __construct(
		public string $access_level,
		string ...$fileName
	) {
		$this->files = $fileName;
	}

	public function addFile(string ...$file): self {
		$this->files = array_merge($this->files, $file);
		return $this;
	}
}
