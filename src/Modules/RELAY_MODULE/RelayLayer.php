<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Nadybot\Core\{Attributes as NCA, DBTable};

#[NCA\DB\Table(name: 'relay_layer')]
class RelayLayer extends DBTable {
	/**
	 * @param string               $layer     Which relay stack layer does this represent?
	 * @param ?int                 $id        The id of the relay layer. Lower id means higher priority
	 * @param ?int                 $relay_id  The id of the relay where this layer belongs to
	 * @param RelayLayerArgument[] $arguments
	 *
	 * @psalm-param list<RelayLayerArgument> $arguments
	 */
	public function __construct(
		public string $layer,
		#[NCA\JSON\Ignore] #[NCA\DB\AutoInc] public ?int $id=null,
		#[NCA\JSON\Ignore] public ?int $relay_id=null,
		#[
			NCA\DB\Ignore,
			CastListToType(RelayLayerArgument::class)
		] public array $arguments=[],
	) {
	}

	/** @param string[] $secrets */
	public function toString(?string $linkType=null, array $secrets=[]): string {
		$arguments = array_map(
			static function (RelayLayerArgument $argument) use ($secrets): string {
				return $argument->toString(in_array($argument->name, $secrets, true));
			},
			$this->arguments
		);
		$argString = '(' . implode(', ', $arguments) . ')';
		if (!isset($linkType)) {
			return $this->layer . $argString;
		}
		return "<a href='chatcmd:///tell <myname> relay list {$linkType} {$this->layer}'>".
			$this->layer . "</a>{$argString}";
	}

	/** @return array<string,string> */
	public function getKVArguments(): array {
		return array_reduce(
			$this->arguments,
			static function (array $kv, RelayLayerArgument $argument): array {
				$kv[$argument->name] = $argument->value;
				return $kv;
			},
			[]
		);
	}
}
