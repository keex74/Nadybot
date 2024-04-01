<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE\Model;

use Nadybot\Core\Modules\DISCORD\ReducedStringableTrait;
use Stringable;

class ConnectionProperties implements Stringable {
	use ReducedStringableTrait;

	public string $os;

	public function __construct(
		?string $os=null,
		public string $browser='Nadybot',
		public string $device='Nadybot',
	) {
		$this->os = $os ?? php_uname('s');
	}
}
