<?php declare(strict_types=1);

namespace Nadybot\Core\Types;

interface ModuleInstanceInterface {
	public function setModuleName(string $name): void;

	public function getModuleName(): string;
}
