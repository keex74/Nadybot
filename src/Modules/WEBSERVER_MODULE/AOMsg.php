<?php declare(strict_types=1);

namespace Nadybot\Modules\WEBSERVER_MODULE;

use stdClass;

class AOMsg {
	public function __construct(
		public string $message,
		public stdClass $popups=new stdClass()
	) {
	}
}
