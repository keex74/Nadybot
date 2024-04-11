<?php declare(strict_types=1);

namespace Nadybot\Modules\WEBSERVER_MODULE;

use Nadybot\Core\Routing\Source;

class WebSource extends Source {
	public function __construct(
		string $type,
		string $name,
		public string $color,
		public ?string $renderAs=null,
		?string $label=null,
		?int $dimension=null
	) {
		parent::__construct(
			type: $type,
			name: $name,
			label: $label,
			dimension: $dimension,
		);
	}
}
