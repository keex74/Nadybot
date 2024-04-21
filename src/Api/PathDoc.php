<?php declare(strict_types=1);

namespace Nadybot\Api;

use Nadybot\Core\Attributes as NCA;

class PathDoc {
	/** @var list<string> */
	public array $tags = [];

	/** @var list<string> */
	public array $methods = [];

	/** @var array<int,NCA\ApiResult> */
	public array $responses = [];
	public ?NCA\RequestBody $requestBody = null;

	public function __construct(
		public string $description,
		public string $path,
	) {
	}
}
