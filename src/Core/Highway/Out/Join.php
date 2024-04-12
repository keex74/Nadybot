<?php declare(strict_types=1);

namespace Nadybot\Core\Highway\Out;

class Join extends OutPackage {
	public function __construct(
		public string $room,
		null|int|string $id=null,
	) {
		parent::__construct(self::JOIN, $id);
	}
}
