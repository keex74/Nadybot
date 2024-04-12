<?php declare(strict_types=1);

namespace Nadybot\Core\Highway\Out;

class Leave extends OutPackage {
	public function __construct(
		public string $room,
		null|int|string $id=null,
	) {
		parent::__construct(self::LEAVE, $id);
	}
}
