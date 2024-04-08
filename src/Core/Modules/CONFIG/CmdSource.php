<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\CONFIG;

class CmdSource {
	/**
	 * A list of permission set mappings this command source maps to
	 *
	 * @var CmdSourceMapping[]
	 */
	public array $mappings = [];

	/**
	 * @param string $source The name/mask of this command source
	 *                       If the name contains an asterisk wildcard, it
	 *                       represents a mask of possible sources, e.g. aotell(*)
	 */
	public function __construct(
		public string $source,
		public bool $has_sub_sources=false,
	) {
	}

	public static function fromMask(string $mask): self {
		$source = $mask;
		$hasSubSources = false;
		if (substr($mask, -3) === '(*)') {
			$source = substr($mask, 0, -3);
			$hasSubSources = true;
		}
		return new self(source: $source, has_sub_sources: $hasSubSources);
	}
}
