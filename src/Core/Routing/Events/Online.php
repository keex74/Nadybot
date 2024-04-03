<?php declare(strict_types=1);

namespace Nadybot\Core\Routing\Events;

use Nadybot\Core\Routing\Character;
use Nadybot\Core\StringableTrait;

class Online extends Base {
	use StringableTrait;

	public const TYPE = 'online';

	public function __construct(
		public ?Character $char=null,
		public ?string $main=null,
		public bool $online=true,
		bool $renderPath=false,
		?string $message=null,
	) {
		parent::__construct(
			type: self::TYPE,
			renderPath: $renderPath,
			message: $message,
		);
	}
}
