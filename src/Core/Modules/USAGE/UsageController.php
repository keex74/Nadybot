<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\USAGE;

use function Safe\json_encode;
use Illuminate\Support\Collection;
use Nadybot\Core\DBSchema\Usage;
use Nadybot\Core\Filesystem;
use Nadybot\Core\{
	Attributes as NCA,
	BotRunner,
	CmdContext,
	Config\BotConfig,
	DB,
	EventManager,
	Exceptions\SQLException,
	ModuleInstance,
	Nadybot,
	ParamClass\PCharacter,
	ParamClass\PDuration,
	ParamClass\PWord,
	SettingManager,
	Text,
	Types\SettingMode,
	Util,
};
use Nadybot\Modules\RELAY_MODULE\{RelayConfig, RelayLayer};

use stdClass;

/**
 * @author Tyrence (RK2)
 * Commands this class contains:
 */
#[
	NCA\Instance,
	NCA\HasMigrations,
	NCA\DefineCommand(
		command: 'usage',
		accessLevel: 'guild',
		description: 'Shows usage stats',
		defaultStatus: 1
	),
]
class UsageController extends ModuleInstance {
	/** Record usage stats */
	#[NCA\Setting\Boolean]
	public bool $recordUsageStats = true;

	/** Botid */
	#[NCA\Setting\Text(mode: SettingMode::NoEdit)]
	public string $botid = '';

	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private SettingManager $settingManager;

	#[NCA\Inject]
	private EventManager $eventManager;

	#[NCA\Inject]
	private Filesystem $fs;

	#[NCA\Inject]
	private Util $util;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private BotConfig $config;

	#[NCA\Inject]
	private Nadybot $chatBot;

	/** Show usage stats for the past 7 days or &lt;duration&gt; for a given character */
	#[NCA\HandlesCommand('usage')]
	public function usageCharacterCommand(
		CmdContext $context,
		#[NCA\Str('char', 'character', 'player')] string $action,
		PCharacter $character,
		?PDuration $duration
	): void {
		$time = 604_800;
		if (isset($duration)) {
			$time = $duration->toSecs();
			if ($time === 0) {
				$msg = 'Please enter a valid time.';
				$context->reply($msg);
				return;
			}
		}

		$timeString = Util::unixtimeToReadable($time);
		$time = time() - $time;

		$query = $this->db->table(Usage::getTable())
			->where('sender', $character())
			->where('dt', '>', $time)
			->groupBy('command')
			->select('command');
		$query->orderByRaw($query->colFunc('COUNT', 'command'))
			->selectRaw($query->colFunc('COUNT', 'command', 'count'));
		$data = $query->asObj(CommandUsageStats::class);
		$count = $data->count();

		if ($count > 0) {
			$blob = '';
			foreach ($data as $row) {
				$blob .= Text::alignNumber($row->count, 3) . " <highlight>{$row->command}<end>\n";
			}

			$msg = $this->text->makeBlob("Usage for {$character} - {$timeString} ({$count})", $blob);
		} else {
			$msg = "No usage statistics found for <highlight>{$character}<end>.";
		}
		$context->reply($msg);
	}

	/** Show usage stats for the past 7 days or &lt;duration&gt; for a given command */
	#[NCA\HandlesCommand('usage')]
	public function usageCmdCommand(
		CmdContext $context,
		#[NCA\Str('cmd')] string $action,
		PWord $cmd,
		?PDuration $duration
	): void {
		$time = 604_800;
		if (isset($duration)) {
			$time = $duration->toSecs();
			if ($time === 0) {
				$msg = 'Please enter a valid time.';
				$context->reply($msg);
				return;
			}
		}

		$timeString = Util::unixtimeToReadable($time);
		$time = time() - $time;

		$cmd = strtolower($cmd());

		$query = $this->db->table(Usage::getTable())
			->where('command', $cmd)
			->where('dt', '>', $time)
			->groupBy('sender');
		$query->orderByColFunc('COUNT', 'sender', 'desc')
			->select('sender', ${$query}->raw($query->colFunc('COUNT', 'command', 'count')));
		$data = $query->asObj(PlayerUsageStats::class)->toArray();
		$count = count($data);

		if ($count > 0) {
			$blob = '';
			foreach ($data as $row) {
				$blob .= Text::alignNumber($row->count, 3) . " <highlight>{$row->sender}<end>\n";
			}

			$msg = $this->text->makeBlob("Usage for {$cmd} - {$timeString} ({$count})", $blob);
		} else {
			$msg = "No usage statistics found for <highlight>{$cmd}<end>.";
		}
		$context->reply($msg);
	}

	/** Show the internal usage data that used to be sent to the Budabot stats server */
	#[NCA\HandlesCommand('usage')]
	public function usageInfoCommand(CmdContext $context, #[NCA\Str('info')] string $action): void {
		$info = $this->getUsageInfo(time() - 7*24*3_600, time());
		$blob = json_encode(
			$info,
			\JSON_PRETTY_PRINT|\JSON_UNESCAPED_SLASHES|\JSON_THROW_ON_ERROR
		);
		$msg = $this->text->makeBlob('Collected usage info', $blob);
		$context->reply($msg);
	}

	/** Show usage stats for the past 7 days or &lt;duration&gt; */
	#[NCA\HandlesCommand('usage')]
	public function usageCommand(CmdContext $context, ?PDuration $duration): void {
		$time = 604_800;
		if (isset($duration)) {
			$time = $duration->toSecs();
			if ($time === 0) {
				$msg = 'Please enter a valid time.';
				$context->reply($msg);
				return;
			}
		}

		$timeString = Util::unixtimeToReadable($time);
		$time = time() - $time;
		$limit = 25;

		// channel usage
		$query = $this->db->table(Usage::getTable())
			->where('dt', '>', $time)
			->groupBy('type')
			->orderBy('type')
			->select('type AS channel');
		$query->selectRaw($query->colFunc('COUNT', 'type', 'count'));

		/** @var ChannelUsageStats[] */
		$data = $query->asObj(ChannelUsageStats::class)->toArray();

		$blob = "<header2>Channel Usage<end>\n";
		foreach ($data as $row) {
			$blob .= "<tab>Number of commands executed in {$row->channel}: <highlight>{$row->count}<end>\n";
		}
		$blob .= "\n";

		// most used commands
		$query = $this->db->table(Usage::getTable())
			->where('dt', '>', $time)
			->groupBy('command')
			->orderByColFunc('COUNT', 'command', 'desc')
			->limit($limit)
			->select('command');
		$query->selectRaw($query->colFunc('COUNT', 'command', 'count'));

		/** @var CommandUsageStats[] */
		$data = $query->asObj(CommandUsageStats::class)->toArray();

		$blob .= "<header2>{$limit} Most Used Commands<end>\n";
		foreach ($data as $row) {
			$commandLink = Text::makeChatcmd($row->command, "/tell <myname> usage cmd {$row->command}");
			$blob .= '<tab>' . Text::alignNumber($row->count, 3).
				" {$commandLink}\n";
		}

		// users who have used the most commands
		$query = $this->db->table(Usage::getTable())
			->where('dt', '>', $time)
			->groupBy('sender')
			->orderByColFunc('COUNT', 'sender', 'desc')
			->limit($limit)
			->select('sender');
		$query->selectRaw($query->colFunc('COUNT', 'sender', 'count'));

		/** @var PlayerUsageStats[] */
		$data = $query->asObj(PlayerUsageStats::class)->toArray();

		$blob .= "\n<header2>{$limit} Most Active Users<end>\n";
		foreach ($data as $row) {
			$senderLink = Text::makeChatcmd($row->sender, "/tell <myname> usage player {$row->sender}");
			$blob .= '<tab>' . Text::alignNumber($row->count, 3).
				" {$senderLink}\n";
		}

		$msg = $this->text->makeBlob("Usage Statistics - {$timeString}", $blob);
		$context->reply($msg);
	}

	/**
	 * Record the use of a command $cmd by player $sender
	 *
	 * @throws SQLException
	 */
	public function record(string $type, string $cmd, string $sender, ?string $handler): void {
		// don't record stats for !grc command or command aliases
		if ($cmd === 'grc' || 'CommandAlias.process' === $handler) {
			return;
		}

		$this->db->insert(new Usage(
			type: $type,
			command: $cmd,
			sender: $sender,
			dt: time(),
		));
	}

	public function getUsageInfo(int $lastSubmittedStats, int $now, bool $debug=false): UsageStats {
		$botid = $this->botid;
		if ($botid === '') {
			$botid = Util::genRandomString(20);
			$this->settingManager->save('botid', $botid);
		}

		$query = $this->db->table(Usage::getTable())
			->where('dt', '>=', $lastSubmittedStats)
			->where('dt', '<', time())
			->groupBy('command')
			->select('command');
		$query->selectRaw($query->rawFunc('COUNT', '*', 'count'));
		$commands = $query->asObj(CommandUsageStats::class)
			->reduce(static function (stdClass $carry, CommandUsageStats $entry) {
				$carry->{$entry->command} = $entry->count;
				return $carry;
			}, new stdClass());

		$fsObj = $this->fs->getFilesystem();
		$fs = new \ReflectionObject($fsObj);
		try {
			$driverProp = $fs->getProperty('driver');
			$fsClass = class_basename($driverProp->getValue($fsObj));
		} catch (\ReflectionException) {
			$fsClass = 'Unknown';
		}

		$settings = new SettingsUsageStats(
			dimension              : $this->config->main->dimension,
			is_guild_bot           : strlen($this->config->general->orgName) > 0,
			guildsize              : $this->getGuildSizeClass(count($this->chatBot->guildmembers)),
			num_workers            : 1 + count($this->config->worker),
			db_type                : $this->db->getType()->value,
			fs_type                : $fsClass,
			bot_version            : BotRunner::getVersion(),
			using_git              : $this->fs->exists(BotRunner::getBasedir() . '/.git'),
			os                     : BotRunner::isWindows() ? 'Windows' : php_uname('s'),
			symbol                 : $this->settingManager->getString('symbol')??'!',
			num_relays             : $this->db->table(RelayConfig::getTable())->count(),
			relay_protocols        : $this->db->table(RelayLayer::getTable())
				->orderBy('relay_id')->orderByDesc('id')->asObj(RelayLayer::class)
				->groupBy('relay_id')
				->map(static function (Collection $group): string {
					return $group->first()->layer;
				})->flatten()->unique()->toArray(),
			aodb_db_version        : $this->settingManager->getString('aodb_db_version')??'unknown',
			max_blob_size          : $this->settingManager->getInt('max_blob_size')??0,
			online_show_org_guild  : $this->settingManager->getInt('online_show_org_guild')??-1,
			online_show_org_priv   : $this->settingManager->getInt('online_show_org_priv')??-1,
			online_admin           : $this->settingManager->getBool('online_admin')??false,
			http_server_enable     : $this->eventManager->getKeyForCronEvent(60, 'httpservercontroller.startHTTPServer') !== null,
		);

		return new UsageStats(
			id: sha1($botid . $this->config->main->character . $this->config->main->dimension),
			version: 2,
			debug: $debug,
			commands: $commands,
			settings: $settings,
		);
	}

	public function getGuildSizeClass(int $size): string {
		$guildClass = 'class7';
		if ($size === 0) {
			$guildClass = 'class0';
		} elseif ($size < 10) {
			$guildClass = 'class1';
		} elseif ($size < 30) {
			$guildClass = 'class2';
		} elseif ($size < 150) {
			$guildClass = 'class3';
		} elseif ($size < 300) {
			$guildClass = 'class4';
		} elseif ($size < 650) {
			$guildClass = 'class5';
		} elseif ($size < 1_000) {
			$guildClass = 'class6';
		} else {
			$guildClass = 'class7';
		}
		return $guildClass;
	}

	#[
		NCA\NewsTile(
			name: 'popular-commands',
			description: "A player's 4 most used commands in the last 7 days",
			example: "<header2>Popular commands<end>\n".
				"<tab>hot\n".
				"<tab>startpage\n".
				"<tab>config\n".
				'<tab>time'
		)
	]
	public function usageNewsTile(string $sender): ?string {
		$commands = $this->db->table(Usage::getTable())
			->where('sender', $sender)
			->where('dt', '>', time() - 7*24*3_600)
			->groupBy('command')
			->orderByColFunc('COUNT', 'command', 'desc')
			->addSelect('command')
			->limit(4)
			->pluckStrings('command');
		if ($commands->isEmpty()) {
			return null;
		}
		$blob = "<header2>Popular commands<end>\n";
		foreach ($commands as $command) {
			$blob .= "<tab>{$command}\n";
		}
		return $blob;
	}
}
