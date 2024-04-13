<?php declare(strict_types=1);

namespace Nadybot\Core\Config;

class Credentials {
	public function __construct(
		public string $login,
		public string $password,
		public string $character,
		public int $dimension,
		public ?string $webLogin=null,
		public ?string $webPassword=null,
	) {
		$this->character = ucfirst(strtolower($this->character));
		if ($this->webLogin === '') {
			$this->webLogin = null;
		}
		if ($this->webPassword === '') {
			$this->webPassword = null;
		}
	}
}
