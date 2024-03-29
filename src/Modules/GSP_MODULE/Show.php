<?php declare(strict_types=1);

namespace Nadybot\Modules\GSP_MODULE;

class Show {
	/**
	 * @param Song[]   $history
	 * @param Stream[] $stream
	 */
	public function __construct(
		public string $date,
		public int $status,
		public int $live=0,
		public string $name='',
		public string $info='',
		public string $deejay='Auto DJ',
		public int $listeners=0,
		public array $history=[],
		public array $stream=[],
	) {
	}
}
