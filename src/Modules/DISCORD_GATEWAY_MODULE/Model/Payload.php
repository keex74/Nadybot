<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE\Model;

use Nadybot\Core\Modules\DISCORD\ReducedStringableTrait;
use Stringable;

class Payload implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param int     $op Opcode for the payload
	 * @param mixed   $d  event data
	 * @param ?int    $s  sequence number, used for resuming sessions and heartbeats
	 * @param ?string $t  the event name for this payload
	 */
	public function __construct(
		public int $op,
		public mixed $d=null,
		public ?int $s=null,
		public ?string $t=null,
	) {
	}
}
