<?php

namespace Budabot\Core;

use stdClass;
use ReflectionAnnotatedMethod;
use Exception;

/**
 * @Instance
 */
class CommandManager {

	/**
	 * @var \Budabot\Core\DB $db
	 * @Inject
	 */
	public $db;

	/**
	 * @var \Budabot\Core\Budabot $chatBot
	 * @Inject
	 */
	public $chatBot;

	/**
	 * @var \Budabot\Core\SettingManager $settingManager
	 * @Inject
	 */
	public $settingManager;

	/**
	 * @var \Budabot\Core\AccessManager $accessManager
	 * @Inject
	 */
	public $accessManager;

	/**
	 * @var \Budabot\Core\HelpManager $helpManager
	 * @Inject
	 */
	public $helpManager;

	/**
	 * @var \Budabot\Core\CommandAlias $commandAlias
	 * @Inject
	 */
	public $commandAlias;

	/**
	 * @var \Budabot\Core\Text $text
	 * @Inject
	 */
	public $text;

	/**
	 * @var \Budabot\Core\Util $util
	 * @Inject
	 */
	public $util;

	/**
	 * @var \Budabot\Core\SubcommandManager $subcommandManager
	 * @Inject
	 */
	public $subcommandManager;
	
	/**
	 * @var \Budabot\Core\CommandSearchController $commandSearchController
	 * @Inject
	 */
	public $commandSearchController;
	
	/**
	 * @var \Budabot\Core\UsageController $usageController
	 * @Inject
	 */
	public $usageController;

	/** @Logger */
	public $logger;

	public $commands;

	/**
	 * @name: register
	 * @description: Registers a command
	 */
	public function register($module, $channel, $filename, $command, $accessLevel, $description, $help='', $defaultStatus=null) {
		$command = strtolower($command);
		$module = strtoupper($module);
		$accessLevel = $this->accessManager->getAccessLevel($accessLevel);

		if (!$this->chatBot->processCommandArgs($channel, $accessLevel)) {
			$this->logger->log('ERROR', "Invalid args for $module:command($command). Command not registered.");
			return;
		}

		if (empty($filename)) {
			$this->logger->log('ERROR', "Error registering $module:command($command).  Handler is blank.");
			return;
		}

		forEach (explode(',', $filename) as $handler) {
			list($name, $method) = explode(".", $handler);
			if (!Registry::instanceExists($name)) {
				$this->logger->log('ERROR', "Error registering method '$handler' for command '$command'.  Could not find instance '$name'.");
				return;
			}
		}

		if (!empty($help)) {
			$help = $this->helpManager->checkForHelpFile($module, $help);
		}

		if ($defaultStatus === null) {
			if ($this->chatBot->vars['default_module_status'] == 1) {
				$status = 1;
			} else {
				$status = 0;
			}
		} else {
			$status = $defaultStatus;
		}

		for ($i = 0; $i < count($channel); $i++) {
			$this->logger->log('debug', "Adding Command to list:($command) File:($filename) Admin:({$accessLevel[$i]}) Channel:({$channel[$i]})");
			$row = $this->db->queryRow("SELECT 1 FROM cmdcfg_<myname> WHERE cmd = ? AND type = ?", $command, $channel[$i]);

			try {
				if ($row !== null) {
					$sql = "UPDATE cmdcfg_<myname> SET `module` = ?, `verify` = ?, `file` = ?, `description` = ?, `help` = ? WHERE `cmd` = ? AND `type` = ?";
					$this->db->exec($sql, $module, '1', $filename, $description, $help, $command, $channel[$i]);
				} else {
					$sql = "INSERT INTO cmdcfg_<myname> (`module`, `type`, `file`, `cmd`, `admin`, `description`, `verify`, `cmdevent`, `status`, `help`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
					$this->db->exec($sql, $module, $channel[$i], $filename, $command, $accessLevel[$i], $description, '1', 'cmd', $status, $help);
				}
			} catch (SQLException $e) {
				$this->logger->log('ERROR', "Error registering method '$handler' for command '$command': " . $e->getMessage());
			}
		}
	}

	/**
	 * @name: activate
	 * @description: Activates a command
	 */
	public function activate($channel, $filename, $command, $accessLevel='all') {
		$command = strtolower($command);
		$accessLevel = $this->accessManager->getAccessLevel($accessLevel);
		$channel = strtolower($channel);

		$this->logger->log('DEBUG', "Activate Command:($command) Admin Type:($accessLevel) File:($filename) Channel:($channel)");

		forEach (explode(',', $filename) as $handler) {
			list($name, $method) = explode(".", $handler);
			if (!Registry::instanceExists($name)) {
				$this->logger->log('ERROR', "Error activating method $handler for command $command.  Could not find instance '$name'.");
				return;
			}
		}

		$obj = new stdClass;
		$obj->file = $filename;
		$obj->admin = $accessLevel;

		$this->commands[$channel][$command] = $obj;
	}

	/**
	 * @name: deactivate
	 * @description: Deactivates a command
	 */
	public function deactivate($channel, $filename, $command) {
		$command = strtolower($command);
		$channel = strtolower($channel);

		$this->logger->log('DEBUG', "Deactivate Command:($command) File:($filename) Channel:($channel)");

		unset($this->commands[$channel][$command]);
	}

	public function updateStatus($channel, $cmd, $module, $status, $admin) {
		if ($channel == 'all' || $channel == '' || $channel == null) {
			$type_sql = '';
		} else {
			$type_sql = "AND `type` = '$channel'";
		}

		if ($cmd == '' || $cmd == null) {
			$cmd_sql = '';
		} else {
			$cmd_sql = "AND `cmd` = '$cmd'";
		}

		if ($module == '' || $module == null) {
			$module_sql = '';
		} else {
			$module_sql = "AND `module` = '$module'";
		}

		if ($admin == '' || $admin == null) {
			$adminSql = '';
		} else {
			$adminSql = ", admin = '$admin'";
		}

		$data = $this->db->query("SELECT * FROM cmdcfg_<myname> WHERE `cmdevent` = 'cmd' $module_sql $cmd_sql $type_sql");
		if (count($data) == 0) {
			return 0;
		}

		forEach ($data as $row) {
			if ($status == 1) {
				$this->activate($row->type, $row->file, $row->cmd, $admin);
			} elseif ($status == 0) {
				$this->deactivate($row->type, $row->file, $row->cmd);
			}
		}

		return $this->db->exec("UPDATE cmdcfg_<myname> SET status = '$status' $adminSql WHERE `cmdevent` = 'cmd' $module_sql $cmd_sql $type_sql");
	}

	/**
	 * @name: loadCommands
	 * @description: Loads the active commands into memory to activate them
	 */
	public function loadCommands() {
		$this->logger->log('DEBUG', "Loading enabled commands");

		$data = $this->db->query("SELECT * FROM cmdcfg_<myname> WHERE `status` = '1' AND `cmdevent` = 'cmd'");
		forEach ($data as $row) {
			$this->activate($row->type, $row->file, $row->cmd, $row->admin);
		}
	}

	public function get($command, $channel=null) {
		$command = strtolower($command);

		if ($channel !== null) {
			$type_sql = "AND type = '{$channel}'";
		}

		$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ? {$type_sql}";
		return $this->db->query($sql, $command);
	}
	
	private function mapToCmd($sc) {
		return $sc->cmd;
	}

	public function process($channel, $message, $sender, CommandReply $sendto) {
		list($cmd, $params) = explode(' ', $message, 2);
		$cmd = strtolower($cmd);

		$commandHandler = $this->getActiveCommandHandler($cmd, $channel, $message);

		// if command doesn't exist
		if ($commandHandler === null) {
			// if they've disabled feedback for guild or private channel, just return
			if (($channel == 'guild' && $this->settingManager->get('guild_channel_cmd_feedback') == 0) || ($channel == 'priv' && $this->settingManager->get('private_channel_cmd_feedback') == 0)) {
				return;
			}

			$similarCommands = $this->commandSearchController->findSimilarCommands(array($cmd));
			$similarCommands = $this->commandSearchController->filterResultsByAccessLevel($sender, $similarCommands);
			$similarCommands = array_slice($similarCommands, 0, 5);
			$cmdNames = array_map(array($this, 'mapToCmd'), $similarCommands);

			$sendto->reply("Error! Unknown command. Did you mean..." . implode(", ", $cmdNames) . '?');
			return;
		}

		// if the character doesn't have access
		if (!$this->checkAccessLevel($channel, $message, $sender, $sendto, $cmd, $commandHandler)) {
			return;
		}

		try {
			$handler = $this->callCommandHandler($commandHandler, $message, $channel, $sender, $sendto);

			if ($handler === null) {
				$help = $this->getHelpForCommand($cmd, $channel, $sender);
				$sendto->reply($help);
			}
		} catch (StopExecutionException $e) {
			throw $e;
		} catch (SQLException $e) {
			$this->logger->log("ERROR", $e->getMessage(), $e);
			$sendto->reply("There was an SQL error executing your command.");
		} catch (Exception $e) {
			$this->logger->log("ERROR", "Error executing '$message': " . $e->getMessage(), $e);
			$sendto->reply("There was an error executing your command: " . $e->getMessage());
		}
		
		try {
			// record usage stats (in try/catch block in case there is an error)
			if ($this->settingManager->get('record_usage_stats') == 1) {
				$this->usageController->record($channel, $cmd, $sender, $handler);
			}
		} catch (Exception $e) {
			$this->logger->log("ERROR", $e->getMessage(), $e);
		}
	}

	public function checkAccessLevel($channel, $message, $sender, $sendto, $cmd, $commandHandler) {
		if ($this->accessManager->checkAccess($sender, $commandHandler->admin) !== true) {
			if ($channel == 'msg') {
				if ($this->settingManager->get('access_denied_notify_guild') == 1) {
					$this->chatBot->sendGuild("Player <highlight>$sender<end> was denied access to command <highlight>$cmd<end>.", true);
				}
				if ($this->settingManager->get('access_denied_notify_priv') == 1) {
					$this->chatBot->sendPrivate("Player <highlight>$sender<end> was denied access to command <highlight>$cmd<end>.", true);
				}
			}
		
			// if they've disabled feedback for guild or private channel, just return
			if (($channel == 'guild' && $this->settingManager->get('guild_channel_cmd_feedback') == 0) || ($channel == 'priv' && $this->settingManager->get('private_channel_cmd_feedback') == 0)) {
				return false;
			}

			$sendto->reply("Error! Access denied.");
			return false;
		}
		return true;
	}

	public function callCommandHandler($commandHandler, $message, $channel, $sender, CommandReply $sendto) {
		$successfulHandler = null;

		forEach (explode(',', $commandHandler->file) as $handler) {
			list($name, $method) = explode(".", $handler);
			$instance = Registry::getInstance($name);
			if ($instance === null) {
				$this->logger->log('ERROR', "Could not find instance for name '$name'");
			} else {
				$arr = $this->checkMatches($instance, $method, $message);
				if ($arr !== false) {
					// methods will return false to indicate a syntax error, so when a false is returned,
					// we set $syntaxError = true, otherwise we set it to false
					$syntaxError = ($instance->$method($message, $channel, $sender, $sendto, $arr) === false);
					if ($syntaxError == false) {
						// we can stop looking, command was handled successfully
						
						$successfulHandler = $handler;
						break;
					}
				}
			}
		}

		return $successfulHandler;
	}

	public function getActiveCommandHandler($cmd, $channel, $message) {
		// Check if a subcommands for this exists
		if (isset($this->subcommandManager->subcommands[$cmd])) {
			forEach ($this->subcommandManager->subcommands[$cmd] as $row) {
				if ($row->type == $channel && preg_match("/^{$row->cmd}$/i", $message)) {
					return $row;
				}
			}
		}
		return $this->commands[$channel][$cmd];
	}

	public function getHelpForCommand($cmd, $channel, $sender) {
		$results = $this->get($cmd, $channel);
		$result = $results[0];

		if ($result->help != '') {
			$blob = file_get_contents($result->help);
		} else {
			$blob = $this->helpManager->find($cmd, $sender);
		}
		if (!empty($blob)) {
			$msg = $this->text->makeBlob("Help ($cmd)", $blob);
		} else {
			$msg = "Error! Invalid syntax.";
		}
		return $msg;
	}

	public function checkMatches($instance, $method, $message) {
		try {
			$reflectedMethod = new ReflectionAnnotatedMethod($instance, $method);
		} catch (ReflectionException $e) {
			// method doesn't exist (probably handled dynamically)
			return true;
		}

		$regexes = $this->retrieveRegexes($reflectedMethod);

		if (count($regexes) > 0) {
			forEach ($regexes as $regex) {
				if (preg_match($regex, $message, $arr)) {
					return $arr;
				}
			}
			return false;
		} else {
			return true;
		}
	}

	public function retrieveRegexes($reflectedMethod) {
		$regexes = array();
		if ($reflectedMethod->hasAnnotation('Matches')) {
			forEach ($reflectedMethod->getAllAnnotations('Matches') as $annotation) {
				$regexes []= $annotation->value;
			}
		}
		return $regexes;
	}
}
