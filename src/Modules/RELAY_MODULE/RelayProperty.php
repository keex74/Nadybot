<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE;

use Nadybot\Core\Attributes\DB\PK;
use Nadybot\Core\{Attributes as NCA, DBTable};

#[NCA\DB\Table(name: 'relay_property')]
class RelayProperty extends DBTable {
	/**
	 * @param int     $relay_id The id of the relay where this layer belongs to
	 * @param string  $property Name of the property
	 * @param ?string $value    The value (in string format)
	 */
	public function __construct(
		#[PK] #[NCA\JSON\Ignore] public int $relay_id,
		#[PK] public string $property,
		public ?string $value=null,
	) {
	}
}
