<?php declare(strict_types=1);

namespace Nadybot\Modules\PACKAGE_MODULE;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class Package {
	/**
	 * @param string               $name              Name of the package
	 * @param string               $description       Long description of the package
	 * @param string               $short_description Short description of the package
	 * @param string               $version           Version is semver notation
	 * @param string               $author            Name of the author
	 * @param string               $bot_type          Required bot type (Nadybot, Budabot, Tyrbot, BeBot)
	 * @param string               $bot_version       Semver range of required bot version(s)
	 * @param ?string              $github            If set, name of the GitHub repo from which to get updates
	 * @param PackageRequirement[] $requires          Array of requirements to run the module
	 */
	public function __construct(
		public string $name,
		public string $description,
		public string $short_description,
		public string $version,
		public string $author,
		public string $bot_type,
		public string $bot_version,
		public ?string $github,
		#[CastListToType(PackageRequirement::class)] public array $requires,
		public bool $compatible=false,
		public int $state=0,
	) {
	}
}
