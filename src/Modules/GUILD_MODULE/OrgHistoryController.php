<?php declare(strict_types=1);

namespace Nadybot\Modules\GUILD_MODULE;

use Amp\Http\HttpStatus;
use Amp\Http\Server\{Request, Response};
use Illuminate\Support\Collection;
use Nadybot\Core\Events\OrgMsgChannelMsgEvent;
use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	DB,
	ModuleInstance,
	ParamClass\PCharacter,
	Safe,
	Text,
	Util,
};
use Nadybot\Modules\WEBSERVER_MODULE\ApiResponse;

/**
 * @author Tyrence (RK2)
 */
#[
	NCA\Instance,
	NCA\HasMigrations('Migrations/History'),
	NCA\DefineCommand(
		command: 'orghistory',
		accessLevel: 'guild',
		description: 'Shows the org history (invites and kicks and leaves) for a character',
	)
]
class OrgHistoryController extends ModuleInstance {
	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	/** Show the last org actions (invite, kick, leave) */
	#[NCA\HandlesCommand('orghistory')]
	public function orgHistoryCommand(CmdContext $context, ?int $page): void {
		$page ??= 1;
		$pageSize = 40;

		$startingRecord = max(0, ($page - 1) * $pageSize);

		$blob = '';

		/** @var Collection<OrgHistory> */
		$data = $this->db->table(OrgHistory::getTable())
			->orderByDesc('time')
			->limit($pageSize)
			->offset($startingRecord)
			->asObj(OrgHistory::class);
		if ($data->count() === 0) {
			$msg = 'No org history has been recorded.';
			$context->reply($msg);
			return;
		}
		foreach ($data as $row) {
			$blob .= $this->formatOrgAction($row);
		}

		$msg = $this->text->makeBlob('Org History', $blob);

		$context->reply($msg);
	}

	/** Show all actions (invite, kick, leave) performed on or by a character */
	#[NCA\HandlesCommand('orghistory')]
	public function orgHistoryPlayerCommand(CmdContext $context, PCharacter $char): void {
		$player = $char();

		$blob = '';

		/** @var Collection<OrgHistory> */
		$data = $this->db->table(OrgHistory::getTable())
			->whereIlike('actee', $player)
			->orderByDesc('time')
			->asObj(OrgHistory::class);
		$count = $data->count();
		$blob .= "\n<header2>Actions on {$player} ({$count})<end>\n";
		foreach ($data as $row) {
			$blob .= $this->formatOrgAction($row);
		}

		/** @var Collection<OrgHistory> */
		$data = $this->db->table(OrgHistory::getTable())
			->whereIlike('actor', $player)
			->orderByDesc('time')
			->asObj(OrgHistory::class);
		$count = $data->count();
		$blob .= "\n<header2>Actions by {$player} ({$count})<end>\n";
		foreach ($data as $row) {
			$blob .= $this->formatOrgAction($row);
		}

		$msg = $this->text->makeBlob("Org History for {$player}", $blob);

		$context->reply($msg);
	}

	public function formatOrgAction(OrgHistory $row): string {
		$time = 'Unknown time';
		if (isset($row->time)) {
			$time = Util::date($row->time);
		}
		if ($row->action === 'left') {
			return "<highlight>{$row->actor}<end> {$row->action}. [{$row->organization}] {$time}\n";
		}
		return "<highlight>{$row->actor}<end> {$row->action} <highlight>{$row->actee}<end>. [{$row->organization}] {$time}\n";
	}

	#[NCA\Event(
		name: OrgMsgChannelMsgEvent::EVENT_MASK,
		description: 'Capture Org Invite/Kick/Leave messages for orghistory'
	)]
	public function captureOrgMessagesEvent(OrgMsgChannelMsgEvent $eventObj): void {
		$message = $eventObj->message;
		if (
			count($arr = Safe::pregMatch('/^(?<actor>.+) just (?<action>left) your organization.$/', $message))
			|| count($arr = Safe::pregMatch('/^(?<actor>.+) (?<action>kicked) (?<actee>.+) from your organization.$/', $message))
			|| count($arr = Safe::pregMatch('/^(?<actor>.+) (?<action>invited) (?<actee>.+) to your organization.$/', $message))
			|| count($arr = Safe::pregMatch('/^(?<actor>.+) (?<action>removed) inactive character (?<actee>.+) from your organization.$/', $message))
		) {
			$this->db->insert(new OrgHistory(
				actor: $arr['actor'] ?? '',
				actee: $arr['actee'] ?? '',
				action: $arr['action'] ?? '',
				organization: $this->db->getMyguild(),
				time: time(),
			));
		}
	}

	/** Query entries from the org history log */
	#[
		NCA\Api('/org/history'),
		NCA\GET,
		NCA\QueryParam(name: 'limit', desc: 'No more than this amount of entries will be returned. Default is 50', type: 'integer'),
		NCA\QueryParam(name: 'offset', desc: 'How many entries to skip before beginning to return entries', type: 'integer'),
		NCA\QueryParam(name: 'actor', desc: 'Show only entries of this actor'),
		NCA\QueryParam(name: 'actee', desc: 'Show only entries with this actee'),
		NCA\QueryParam(name: 'action', desc: 'Show only entries with this action'),
		NCA\QueryParam(name: 'before', desc: 'Show only entries from before the given timestamp', type: 'integer'),
		NCA\QueryParam(name: 'after', desc: 'Show only entries from after the given timestamp', type: 'integer'),
		NCA\AccessLevel('mod'),
		NCA\ApiTag('audit'),
		NCA\ApiResult(code: 200, class: 'OrgHistory[]', desc: 'The org history log entries')
	]
	public function historyGetListEndpoint(Request $request): Response {
		$query = $this->db->table(OrgHistory::getTable())
			->orderByDesc('time')
			->orderByDesc('id');

		$limit = $request->getQueryParameter('limit')??'50';
		if (!ctype_digit($limit)) {
			return new Response(
				status: HttpStatus::UNPROCESSABLE_ENTITY,
				body: 'limit is not an integer value'
			);
		}
		$query->limit((int)$limit);

		$offset = $request->getQueryParameter('offset');
		if (isset($offset)) {
			if (!ctype_digit($offset)) {
				return new Response(
					status: HttpStatus::UNPROCESSABLE_ENTITY,
					body: 'offset is not an integer value'
				);
			}
			$query->offset((int)$offset);
		}

		$before = $request->getQueryParameter('before');
		if (isset($before)) {
			if (!ctype_digit($before)) {
				return new Response(
					status: HttpStatus::UNPROCESSABLE_ENTITY,
					body: 'before is not an integer value'
				);
			}
			$query->where('time', '<=', $before);
		}

		$after = $request->getQueryParameter('after');
		if (isset($after)) {
			if (!ctype_digit($after)) {
				return new Response(
					status: HttpStatus::UNPROCESSABLE_ENTITY,
					body: 'after is not an integer value'
				);
			}
			$query->where('time', '>=', $after);
		}

		$actor = $request->getQueryParameter('actor');
		if (isset($actor)) {
			$query->where('actor', ucfirst(strtolower($actor)));
		}

		$actee = $request->getQueryParameter('actee');
		if (isset($actee)) {
			$query->where('actee', ucfirst(strtolower($actee)));
		}

		$action = $request->getQueryParameter('action');
		if (isset($action)) {
			$query->where('action', strtolower($action));
		}

		return ApiResponse::create($query->asObj(OrgHistory::class)->toArray());
	}
}
