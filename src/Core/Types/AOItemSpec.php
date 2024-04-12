<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

interface AOItemSpec {
	public function getLowID(): int;

	public function getHighID(): int;

	public function getLowQL(): int;

	public function getHighQL(): int;

	public function getName(): string;

	public function getLink(?int $ql=null, ?string $text=null): string;

	public function atQL(int $ql): AOItem;
}
