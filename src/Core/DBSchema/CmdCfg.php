<?php declare(strict_types=1);

namespace Nadybot\Core\DBSchema;

use Nadybot\Core\{Attributes as NCA, DBRow};

#[NCA\DB\Table(name: 'cmdcfg')]
class CmdCfg extends DBRow {
	/**
	 * @var array<string,CmdPermission>
	 *
	 * @json-var CmdPermission[]
	 */
	#[NCA\DB\Ignore]
	#[NCA\JSON\Map('array_values')]
	public array $permissions = [];

	public function __construct(
		#[NCA\JSON\Ignore] public string $module,
		#[NCA\JSON\Ignore] public string $cmdevent,
		#[NCA\JSON\Ignore] public string $file,
		#[NCA\DB\PK] public string $cmd,
		public string $description='none',
		#[NCA\JSON\Ignore] public int $verify=0,
		#[NCA\JSON\Ignore] public string $dependson='none',
	) {
	}
}
