<?php declare(strict_types=1);

namespace Nadybot\Modules\WEBSOCKET_MODULE;

class NadyRequest {
	public const READ = 1;
	public const WRITE = 2;
	public const CREATE = 3;

	/** @param ?mixed[] $data */
	public function __construct(
		public string $resource='/',
		public int $mode=self::READ,
		public ?array $data=null,
	) {
	}
}
