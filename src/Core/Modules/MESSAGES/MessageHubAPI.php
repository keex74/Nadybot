<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\MESSAGES;

use Amp\Http\Server\{Request, Response};
use Nadybot\Core\{
	Attributes as NCA,
	MessageHub,
	ModuleInstance,
	Routing\Source,
};
use Nadybot\Modules\WEBSERVER_MODULE\ApiResponse;

/**
 * @author Nadyita (RK5)
 */
#[NCA\Instance]
class MessageHubAPI extends ModuleInstance {
	/** List all hop colors */
	#[
		NCA\Api("/hop/color"),
		NCA\GET,
		NCA\AccessLevel("all"),
		NCA\ApiResult(code: 200, class: "RouteHopColor[]", desc: "The hop color definitions")
	]
	public function apiGetHopColors(Request $request): Response {
		return ApiResponse::create(MessageHub::$colors->toArray());
	}

	/** List all hop formats */
	#[
		NCA\Api("/hop/format"),
		NCA\GET,
		NCA\AccessLevel("all"),
		NCA\ApiResult(code: 200, class: "RouteHopFormat[]", desc: "The hop format definitions")
	]
	public function apiGetHopFormats(Request $request): Response {
		return ApiResponse::create(Source::$format->toArray());
	}
}
