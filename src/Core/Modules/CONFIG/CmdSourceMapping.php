<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\CONFIG;

use Nadybot\Core\Attributes\JSON;

use Nadybot\Core\DBSchema\CmdPermSetMapping;
use Nadybot\Core\Safe;

class CmdSourceMapping {
	/** The name of this command source */
	#[JSON\Ignore]
	public string $source;

	/** The value for the sub-source, or null if none */
	public ?string $sub_source = null;

	/** The permission set to map $source to */
	public string $permission_set;

	/** The prefix that triggers a command if it's the first letter */
	public string $cmd_prefix;

	/** Is the prefix required to interpret the msg as command or optional */
	public bool $cmd_prefix_optional = false;

	/** Shall we report an error if the command doesn't exist */
	public bool $unknown_cmd_feedback = true;

	public static function fromPermSetMapping(CmdPermSetMapping $map): self {
		$res = new self();
		if (count($matches = Safe::pregMatch("/^(.*?)\((.*)\)$/", $map->source)) === 3) {
			$res->source = $matches[1];
			$res->sub_source = $matches[2];
		} else {
			$res->source = $map->source;
		}
		$res->cmd_prefix = $map->symbol;
		$res->cmd_prefix_optional = $map->symbol_optional;
		$res->permission_set = $map->permission_set;
		$res->unknown_cmd_feedback = $map->feedback;
		return $res;
	}

	public function toPermSetMapping(): CmdPermSetMapping {
		$source = $this->source . (isset($this->sub_source) ? "({$this->sub_source})" : '');
		return new CmdPermSetMapping(
			source: $source,
			permission_set: $this->permission_set,
			feedback: $this->unknown_cmd_feedback,
			symbol: $this->cmd_prefix ?? '!',
			symbol_optional: $this->cmd_prefix_optional,
		);
	}
}
