<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE;

use function Safe\preg_split;

use EventSauce\ObjectHydrator\ObjectMapperUsingReflection;
use Illuminate\Support\Collection;
use Nadybot\Core\Modules\DISCORD\{ApplicationCommand, ApplicationCommandOption, DiscordException};
use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	CommandManager,
	DB,
	DBSchema\CmdCfg,
	Exceptions\UserException,
	MessageHub,
	ModuleInstance,
	Modules\DISCORD\DiscordAPIClient,
	Modules\DISCORD\DiscordChannel,
	Nadybot,
	ParamClass\Base,
	ParamClass\PRemove,
	Registry,
	Routing\Character,
	Routing\RoutableMessage,
	Routing\Source,
	Text,
};
use Nadybot\Modules\DISCORD_GATEWAY_MODULE\Model\Interaction;
use Psr\Log\LoggerInterface;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Throwable;

#[
	NCA\Instance,
	NCA\DefineCommand(
		command: 'discord slash-commands',
		accessLevel: 'mod',
		description: 'Manage the exposed Discord slash-commands',
	),
]
class DiscordSlashCommandController extends ModuleInstance {
	/** Slash-commands are disabled */
	public const SLASH_OFF = 0;

	/** Slash-commands are treated like regular commands and shown to everyone */
	public const SLASH_REGULAR = 1;

	/** Slash-commands are only shown to the sender */
	public const SLASH_EPHEMERAL = 2;

	public const APP_TYPE_NO_PARAMS = 0;
	public const APP_TYPE_OPT_PARAMS = 1;
	public const APP_TYPE_REQ_PARAMS = 2;

	/** How to handle Discord Slash-commands */
	#[NCA\Setting\Options(options: [
		'Disable' => 0,
		'Treat them like regular commands' => 1,
		'Make request and reply private' => 2,
	])]
	public int $discordSlashCommands = self::SLASH_EPHEMERAL;

	#[NCA\Logger]
	private LoggerInterface $logger;

	#[NCA\Inject]
	private CommandManager $cmdManager;

	#[NCA\Inject]
	private DiscordAPIClient $api;

	#[NCA\Inject]
	private DiscordGatewayController $gw;

	#[NCA\Inject]
	private DiscordGatewayCommandHandler $gwCmd;

	#[NCA\Inject]
	private MessageHub $messageHub;

	#[NCA\Inject]
	private Nadybot $chatBot;

	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	/** If the state changes to/from disabled, then we need to re-register the slash-cmds */
	#[NCA\SettingChangeHandler('discord_slash_commands')]
	public function syncSlashCmdsOnStateChange(string $settingName, string $oldValue, string $newValue): void {
		if ((int)$oldValue !== self::SLASH_OFF && (int)$newValue !== self::SLASH_OFF) {
			return;
		}
		$this->syncSlashCommands();
	}

	/** Make sure, all slash-commands that the bot has configured, are registered */
	public function syncSlashCommands(): void {
		$appId = $this->gw->getID();
		if (!isset($appId)) {
			return;
		}

		$registeredCommands = $this->api->getGlobalApplicationCommands($appId);
		$this->updateSlashCommands($registeredCommands);
	}

	/**
	 * Calculate which slash-commands should be enabled
	 * and return them as an array of ApplicationCommands
	 *
	 * @return list<ApplicationCommand>
	 */
	public function calcSlashCommands(): array {
		$enabledCommands = $this->db->table(DiscordSlashCommand::getTable())
			->pluckStrings('cmd')->toArray();
		if ($this->discordSlashCommands === self::SLASH_OFF) {
			return [];
		}

		/** @var list<ApplicationCommand> */
		$cmds = [];
		$cmdDefs = $this->getCmdDefinitions(...$enabledCommands);
		foreach ($cmdDefs as $cmdCfg) {
			if (($appCmd = $this->getApplicationCommandForCmdCfg($cmdCfg)) !== null) {
				$cmds []= $appCmd;
			}
		}
		return $cmds;
	}

	/** Show all currently exposed Discord slash-commands */
	#[NCA\HandlesCommand('discord slash-commands')]
	public function listDiscordSlashCommands(
		CmdContext $context,
		#[NCA\Str('slash')] string $action,
		#[NCA\Str('list')] ?string $subAction
	): void {
		$cmds = $this->db->table(DiscordSlashCommand::getTable())
			->orderBy('cmd')
			->pluckStrings('cmd');
		$lines = $cmds->map(static function (string $cmd): string {
			$delCommand = Text::makeChatcmd(
				'remove',
				"/tell <myname> discord slash rem {$cmd}",
			);
			return "<tab>{$cmd} [{$delCommand}]";
		});
		if ($lines->isEmpty()) {
			$context->reply('Registered Slash-commands (0)');
			return;
		}
		$blob = "<header2>Currently registered Slash-commands<end>\n".
			$lines->join("\n");
		$context->reply($this->text->makeBlob(
			'Registered Slash-commands (' . $lines->count() . ')',
			$blob
		));
	}

	/** Add one or more commands to the list of Discord slash-commands */
	#[NCA\HandlesCommand('discord slash-commands')]
	public function addDiscordSlashCommands(
		CmdContext $context,
		#[NCA\Str('slash')] string $action,
		#[NCA\Str('add')] string $subAction,
		#[NCA\PWord] string ...$commands,
	): void {
		$cmds = $this->db->table(DiscordSlashCommand::getTable())
			->orderBy('cmd')
			->pluckStrings('cmd')
			->toArray();
		$newCommands = collect($commands)
			->map(static function (string $cmd): string {
				return strtolower($cmd);
			})
			->unique()
			->filter(static function (string $cmd) use ($cmds): bool {
				return !in_array($cmd, $cmds);
			});
		$illegalCommands = $newCommands
			->filter(function (string $cmd): bool {
				foreach ($this->cmdManager->commands as $permSet => $cmds) {
					if (isset($cmds[$cmd])) {
						return false;
					}
				}
				return true;
			});
		if ($illegalCommands->isNotEmpty()) {
			$msg = "The following command doesn't exist or is not enabled: %s";
			if ($illegalCommands->count() !== 1) {
				$msg = "The following commands don't exist or aren't enabled: %s";
			}
			$errors = Text::arraySprintf('<highlight>%s<end>', ...$illegalCommands->toArray());
			$context->reply(sprintf($msg, Text::enumerate(...$errors)));
			return;
		}
		if ($newCommands->isEmpty()) {
			$context->reply('All given commands are already exposed as Slash-commands.');
			return;
		}
		if (count($cmds) + $newCommands->count() > 100) {
			$context->reply('You can only expose a total of 100 commands.');
			return;
		}
		$cmdText = $newCommands->containsOneItem() ? 'command' : 'commands';
		if (!$this->db->insert(
			$newCommands->map(static function (string $cmd): DiscordSlashCommand {
				return new DiscordSlashCommand(cmd: $cmd);
			})
		)) {
			$context->reply("There was an error registering the {$cmdText}.");
			return;
		}
		$context->reply('Trying to add ' . $newCommands->count() . " {$cmdText}...");
		try {
			$this->syncSlashCommands();
		} catch (Throwable $e) {
			$this->db->table(DiscordSlashCommand::getTable())
				->whereIn('cmd', $newCommands->toArray())
				->delete();
			$context->reply(
				'Error registering ' . $newCommands->count(). ' new '.
				"Slash-{$cmdText}: " . $e->getMessage()
			);
			return;
		}
		$context->reply(
			'Successfully registered ' . $newCommands->count(). ' new '.
			"Slash-{$cmdText}."
		);
	}

	/** Remove one or more commands from the list of Discord slash-commands */
	#[NCA\HandlesCommand('discord slash-commands')]
	public function remDiscordSlashCommands(
		CmdContext $context,
		#[NCA\Str('slash')] string $action,
		PRemove $subAction,
		#[NCA\PWord] string ...$commands,
	): void {
		$cmds = $this->db->table(DiscordSlashCommand::getTable())
			->orderBy('cmd')
			->pluckStrings('cmd')
			->toArray();
		$delCommands = collect($commands)
			->map(static function (string $cmd): string {
				return strtolower($cmd);
			})
			->unique()
			->filter(static function (string $cmd) use ($cmds): bool {
				return in_array($cmd, $cmds);
			});
		if ($delCommands->isEmpty()) {
			$context->reply('None of the given commands are currently exposed as Slash-commands.');
			return;
		}
		$cmdText = $delCommands->containsOneItem() ? 'command' : 'commands';
		$this->db->table(DiscordSlashCommand::getTable())
			->whereIn('cmd', $delCommands->toArray())
			->delete();
		$context->reply('Trying to remove ' . $delCommands->count() . " {$cmdText}...");
		try {
			$this->syncSlashCommands();
		} catch (Throwable $e) {
			$this->db->insert(
				$delCommands->map(static function (string $cmd): DiscordSlashCommand {
					return new DiscordSlashCommand(cmd: $cmd);
				})
			);
			$context->reply(
				'Error removing ' . $delCommands->count(). ' '.
				"Slash-{$cmdText}: " . $e->getMessage()
			);
			return;
		}
		$context->reply(
			'Successfully removed ' . $delCommands->count(). ' '.
			"Slash-{$cmdText}."
		);
	}

	/** Pick commands to add to the list of Discord slash-commands */
	#[NCA\HandlesCommand('discord slash-commands')]
	public function pickDiscordSlashCommands(
		CmdContext $context,
		#[NCA\Str('slash')] string $action,
		#[NCA\Str('pick')] string $subAction,
	): void {
		$exposedCmds = $this->db->table(DiscordSlashCommand::getTable())
			->orderBy('cmd')
			->pluckStrings('cmd')
			->toList();

		/** @var Collection<int,CmdCfg> */
		$cmds = new Collection($this->cmdManager->getAll(false));

		$parts = $cmds
			->sortBy('module')
			->filter(static function (CmdCfg $cmd) use ($exposedCmds): bool {
				return !in_array($cmd->cmd, $exposedCmds);
			})->groupBy('module')
			->map(static function (Collection $cmds, string $module): string {
				$lines = $cmds->sortBy('cmd')->map(static function (CmdCfg $cmd): string {
					$addLink = Text::makeChatcmd(
						'add',
						"/tell <myname> discord slash add {$cmd->cmd}"
					);
					return "<tab>[{$addLink}] <highlight>{$cmd->cmd}<end>: {$cmd->description}";
				});
				return "<pagebreak><header2>{$module}<end>\n".
					$lines->join("\n");
			});
		$blob = $parts->join("\n\n");
		$context->reply($this->text->makeBlob(
			'Pick from available commands (' . $cmds->count() . ')',
			$blob,
		));
	}

	/** Handle an incoming discord channel message */
	#[NCA\Event(
		name: 'discord(interaction_create)',
		description: 'Handle Discord slash commands'
	)]
	public function handleSlashCommands(DiscordGatewayEvent $event): void {
		$payload = $event->payload;
		if (!isset($payload->d) || !is_array($payload->d)) {
			return;
		}
		$this->logger->info('Received interaction on Discord');
		$mapper = new ObjectMapperUsingReflection();
		$interaction = $mapper->hydrateObject(Interaction::class, $payload->d);
		$this->logger->debug('Interaction decoded', [
			'interaction' => $interaction,
		]);
		if (!$this->gw->isMe($interaction->application_id)) {
			$this->logger->info('Interaction is not for this bot');
			return;
		}
		$discordUserId = $interaction->user->id ?? $interaction->member->user->id ?? null;
		if (!isset($discordUserId)) {
			$this->logger->info('Interaction has no user id set');
			return;
		}
		if ($interaction->type === $interaction::TYPE_APPLICATION_COMMAND
			&& $this->discordSlashCommands === self::SLASH_OFF) {
			$this->logger->info('Ignoring disabled slash-command');
			return;
		}
		if ($interaction->type !== $interaction::TYPE_APPLICATION_COMMAND
			&& $interaction->type !== $interaction::TYPE_MESSAGE_COMPONENT) {
			$this->logger->info('Ignoring unuspported interaction type');
			return;
		}
		$cmd = $interaction->toCommand();
		if (!isset($cmd)) {
			$this->logger->info('No command to execute found in interaction');
			return;
		}
		$context = new CmdContext(
			charName: $discordUserId,
			isDM: isset($interaction->user),
			message: $cmd,
		);
		if (isset($interaction->channel_id)) {
			$channel = $this->gw->getChannel($interaction->channel_id);
			if (!isset($channel)) {
				$this->logger->info('Interaction is for an unknown channel');
				return;
			}
			$context->source = Source::DISCORD_PRIV . "({$channel->name})";
			$cmdMap = $this->cmdManager->getPermsetMapForSource($context->source);
			if (!isset($cmdMap)) {
				$this->logger->info('No permission set found for {source}', [
					'source' => $context->source,
				]);
				$context->source = Source::DISCORD_PRIV . "({$channel->id})";
				$cmdMap = $this->cmdManager->getPermsetMapForSource($context->source);
			}
		} else {
			$context->source = Source::DISCORD_MSG . "({$discordUserId})";
			$cmdMap = $this->cmdManager->getPermsetMapForSource($context->source);
		}
		if (!isset($cmdMap)) {
			$this->logger->info('No permission set found for {source}', [
				'source' => $context->source,
			]);
			return;
		}
		$context->message = $cmdMap->symbol . $context->message;
		$this->executeSlashCommand($interaction, $context);
	}

	/**
	 * Ensure the global application commands are identical to $registeredCmds
	 *
	 * @param iterable<array-key,ApplicationCommand> $registeredCmds
	 */
	private function updateSlashCommands(iterable $registeredCmds): void {
		$registeredCmds = collect($registeredCmds);
		$this->logger->info('{count} Slash-commands already registered', [
			'count' => $registeredCmds->count(),
		]);

		/** @var Collection<int,ApplicationCommand> */
		$commands = collect($this->calcSlashCommands());

		$numModifiedCommands = $this->getNumChangedSlashCommands($registeredCmds, $commands);
		$this->logger->info('{count} Slash-commands need (re-)registering', [
			'count' => $numModifiedCommands,
		]);

		if ($registeredCmds->count() === $commands->count() && $numModifiedCommands === 0) {
			$this->logger->info('No Slash-commands need (re-)registering or deletion');
			return;
		}
		$this->setSlashCommands($commands);
	}

	/**
	 * Set the given slash commands without checking if they've changed
	 *
	 * @param Collection<int,ApplicationCommand> $modifiedCommands
	 */
	private function setSlashCommands(Collection $modifiedCommands): void {
		$appId = $this->gw->getID();
		if (!isset($appId)) {
			throw new UserException('Currently not connected to Discord, try again later.');
		}
		$cmds = $modifiedCommands->toArray();
		try {
			$newCmds = $this->api->registerGlobalApplicationCommands(
				$appId,
				$this->api->encode($cmds)
			);
		} catch (DiscordException $e) {
			if ($e->getCode() === 403) {
				throw new UserException('The Discord bot lacks the right to manage slash commands.');
			}
			throw $e;
		}
		$this->logger->notice('{num_commands} Slash-commands registered successfully.', [
			'num_commands' => count($newCmds),
		]);
	}

	/** @return array<string,CmdCfg> */
	private function getCmdDefinitions(string ...$commands): array {
		$cfgs = $this->db->table(CmdCfg::getTable())
			->whereIn('cmd', $commands)
			->orWhereIn('dependson', $commands)
			->asObj(CmdCfg::class);

		/** @var Collection<string,CmdCfg> */
		$mains = $cfgs->where('cmdevent', 'cmd')
			->keyBy('cmd');
		$cfgs->where('cmdevent', 'subcmd')
			->each(static function (CmdCfg $cfg) use ($mains): void {
				$search = $mains->get($cfg->dependson);
				if (!isset($search)) {
					return;
				}
				$search->file .= ",{$cfg->file}";
			});
		return $mains->toArray();
	}

	/** Get the ApplicationCommand-definition for a single NCA\DefineCommand */
	private function getApplicationCommandForCmdCfg(CmdCfg $cmdCfg): ?ApplicationCommand {
		$cmd = new ApplicationCommand(
			id: null,
			application_id: null,
			version: null,
			guild_id: null,
			type: ApplicationCommand::TYPE_CHAT_INPUT,
			name: $cmdCfg->cmd,
			description: $cmdCfg->description,
		);

		/** @var list<int> */
		$types = [];
		$methods = explode(',', $cmdCfg->file);
		foreach ($methods as $methodDef) {
			[$class, $method, $line] = preg_split('/[.:]/', $methodDef);
			$obj = Registry::tryGetInstance($class);
			if (!isset($obj)) {
				continue;
			}
			$refMethod = new ReflectionMethod($obj, $method);
			$type = $this->getApplicationCommandOptionType($refMethod);
			if (isset($type)) {
				$types []= $type;
			}
		}
		if (!count($types)) {
			return null;
		}
		if (count($types) === 1 && $types[0] === 0) {
			return $cmd;
		}

		$option = new ApplicationCommandOption(
			name: 'parameters',
			description: 'Parameters for this command',
			type: ApplicationCommandOption::TYPE_STRING,
			required: min($types) === self::APP_TYPE_REQ_PARAMS,
		);
		$cmd->options = [$option];

		return $cmd;
	}

	/**
	 * Calculate if the given command doesn't require parameters (0), has
	 * optional parameters (1) or mandatory parameters (2)
	 *
	 * @phpstan-return null|self::APP_TYPE_*
	 */
	private function getApplicationCommandOptionType(ReflectionMethod $refMethod): ?int {
		$params = $refMethod->getParameters();
		if (count($params) === 0
			|| !$params[0]->hasType()) {
			return null;
		}
		$type = $params[0]->getType();
		if (!($type instanceof ReflectionNamedType)
			|| ($type->getName() !== CmdContext::class)) {
			return null;
		}
		if (count($params) === 1) {
			return self::APP_TYPE_NO_PARAMS;
		}

		$type = self::APP_TYPE_OPT_PARAMS;
		for ($i = 1; $i < count($params); $i++) {
			$paramType = $this->getParamOptionType($params[$i], count($params));
			if ($paramType === null) {
				return null;
			}
			$type = max($type, $paramType);
		}
		return $type;
	}

	/**
	 * Calculate if the given parameter is optional(1) or mandatory(2)
	 *
	 * @phpstan-return null|self::APP_TYPE_OPT_PARAMS|self::APP_TYPE_REQ_PARAMS
	 */
	private function getParamOptionType(ReflectionParameter $param, int $numParams): ?int {
		if (!$param->hasType()) {
			return null;
		}
		$type = $param->getType();
		if (!($type instanceof ReflectionNamedType)) {
			return null;
		}
		if (!$type->isBuiltin() && !is_subclass_of($type->getName(), Base::class)) {
			return null;
		}
		if ($param->allowsNull()) {
			return self::APP_TYPE_OPT_PARAMS;
		}
		return self::APP_TYPE_REQ_PARAMS;
	}

	/**
	 * Calculate how many commands in $set have change relatively to $live
	 *
	 * @param Collection<int,ApplicationCommand> $live
	 * @param Collection<int,ApplicationCommand> $set
	 */
	private function getNumChangedSlashCommands(Collection $live, Collection $set): int {
		$live = $live->keyBy('name');
		$changedOrNewCommands = $set->filter(static function (ApplicationCommand $cmd) use ($live): bool {
			/** @psalm-suppress PossiblyNullArgument */
			return !$live->has($cmd->name)
				|| !$cmd->isSameAs($live->get($cmd->name));
		})->values();
		return $changedOrNewCommands->count();
	}

	/** Execute the given interaction/slash-command */
	private function executeSlashCommand(Interaction $interaction, CmdContext $context): void {
		$discordUserId = $interaction->user->id ?? $interaction->member->user->id ?? null;
		if ($discordUserId === null) {
			$this->logger->info('Interaction has no user id set');
			return;
		}
		$sendto = new DiscordSlashCommandReply(
			$interaction->application_id,
			$interaction->id,
			$interaction->token,
			$interaction->channel_id,
			$context->isDM(),
		);
		Registry::injectDependencies($sendto);
		$context->sendto = $sendto;
		$sendto->sendStateUpdate();
		$userId = $this->gwCmd->getNameForDiscordId($discordUserId);
		// Create and route an artificial message if slash-commands are
		// treated like regular commands
		if (isset($interaction->channel_id)
			&& $this->discordSlashCommands === self::SLASH_REGULAR
		) {
			$channel = $this->gw->lookupChannel($interaction->channel_id);
			if (isset($channel)) {
				$this->createAndRouteSlashCmdChannelMsg($channel, $context, $userId ?? $discordUserId);
			}
		}

		$this->logger->info('Executing slash-command "{command}" from {source}', [
			'command' => $context->message,
			'source' => $context->source,
		]);
		// Do the actual command execution
		$execCmd = function () use ($context): void {
			$this->cmdManager->checkAndHandleCmd($context);
		};
		if (!isset($userId)) {
			$execCmd();
			return;
		}
		$context->char->name = $userId;
		$uid = $this->chatBot->getUid($userId);
		$context->char->id = $uid;
		$execCmd();
	}

	/**
	 * Because slash-command-requests are not messages, we have to create
	 * a message ourselves and route it to the bot - if it was issued on a channel
	 * This is just a message with the command that was given
	 */
	private function createAndRouteSlashCmdChannelMsg(DiscordChannel $channel, CmdContext $context, string $userId): int {
		$this->logger->info('Create and route stub-message for slash-command');
		$rMessage = new RoutableMessage('/' . substr($context->message, 1));
		$rMessage->setCharacter(
			new Character($userId, null, null)
		);
		$rMessage->appendPath(
			new Source(
				Source::DISCORD_PRIV,
				$channel->name ?? $channel->id,
			),
		);
		return $this->messageHub->handle($rMessage);
	}
}
