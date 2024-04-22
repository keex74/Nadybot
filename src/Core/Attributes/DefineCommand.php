<?php declare(strict_types=1);

namespace Nadybot\Core\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::IS_REPEATABLE)]
class DefineCommand {
	/** @param null|string|list<string> $alias */
	public function __construct(
		public string $command,
		public string $description,
		public ?string $accessLevel=null,
		public ?string $help=null,
		public ?int $defaultStatus=null,
		public null|string|array $alias=null
	) {
	}
}
