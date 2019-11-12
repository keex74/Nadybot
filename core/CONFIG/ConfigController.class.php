<?php

namespace Budabot\Core\Modules;

use ReflectionClass;
use Budabot\Core\Registry;

/**
 * @Instance
 */
class ConfigController {

	/**
	 * Name of the module.
	 * Set automatically by module loader.
	 */
	public $moduleName;

	/**
	 * @var \Budabot\Core\Text $text
	 * @Inject
	 */
	public $text;

	/**
	 * @var \Budabot\Core\DB $db
	 * @Inject
	 */
	public $db;

	/**
	 * @var \Budabot\Core\CommandManager $commandManager
	 * @Inject
	 */
	public $commandManager;

	/**
	 * @var \Budabot\Core\EventManager $eventManager
	 * @Inject
	 */
	public $eventManager;

	/**
	 * @var \Budabot\Core\SubcommandManager $subcommandManager
	 * @Inject
	 */
	public $subcommandManager;

	/**
	 * @var \Budabot\Core\CommandAlias $commandAlias
	 * @Inject
	 */
	public $commandAlias;

	/**
	 * @var \Budabot\Core\HelpManager $helpManager
	 * @Inject
	 */
	public $helpManager;

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
	
	/** @Logger */
	public $logger;

	/**
	 * @Setup
	 * This handler is called on bot startup.
	 */
	public function setup() {

		// construct list of command handlers
		$filename = array();
		$reflectedClass = new ReflectionClass($this);
		$className = Registry::formatName(get_class($this));
		forEach ($reflectedClass->getMethods() as $reflectedMethod) {
			if (preg_match('/command$/i', $reflectedMethod->name)) {
				$filename []= "{$className}.{$reflectedMethod->name}";
			}
		}
		$filename = implode(',', $filename);

		$this->commandManager->activate("msg", $filename, "config", "mod");
		$this->commandManager->activate("guild", $filename, "config", "mod");
		$this->commandManager->activate("priv", $filename, "config", "mod");

		$this->helpManager->register($this->moduleName, "config", "config.txt", "mod", "Configure Commands/Events");
	}

	/**
	 * This command handler lists list of modules which can be configured.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config$/i")
	 */
	public function configCommand($message, $channel, $sender, $sendto, $args) {
		$blob =
			"Org Commands - " .
				$this->text->makeChatcmd('Enable All', '/tell <myname> config cmd enable guild') . " " .
				$this->text->makeChatcmd('Disable All', '/tell <myname> config cmd disable guild') . "\n" .
			"Private Channel Commands - " .
				$this->text->makeChatcmd('Enable All', '/tell <myname> config cmd enable priv') . " " .
				$this->text->makeChatcmd('Disable All', '/tell <myname> config cmd disable priv') . "\n" .
			"Private Message Commands - " .
				$this->text->makeChatcmd('Enable All', '/tell <myname> config cmd enable msg') . " " .
				$this->text->makeChatcmd('Disable All', '/tell <myname> config cmd disable msg') . "\n" .
			"ALL Commands - " .
				$this->text->makeChatcmd('Enable All', '/tell <myname> config cmd enable all') . " " .
				$this->text->makeChatcmd('Disable All', '/tell <myname> config cmd disable all') . "\n\n\n";
	
		$sql = "
			SELECT
				module,
				SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) count_enabled,
				SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) count_disabled
			FROM
				(SELECT module, status FROM cmdcfg_<myname> WHERE `cmdevent` = 'cmd'
				UNION
				SELECT module, status FROM eventcfg_<myname>
				UNION
				SELECT module, 2 FROM settings_<myname>) t
			GROUP BY
				module
			ORDER BY
				module ASC";
	
		$data = $this->db->query($sql);
		$count = count($data);
		forEach ($data as $row) {
			if ($row->count_enabled > 0 && $row->count_disabled > 0) {
				$a = "(<yellow>Partial<end>)";
			} elseif ($row->count_disabled == 0) {
				$a = "(<green>Running<end>)";
			} else {
				$a = "(<red>Disabled<end>)";
			}
	
			$c = "(" . $this->text->makeChatcmd("Configure", "/tell <myname> config $row->module") . ")";
	
			$on = $this->text->makeChatcmd("On", "/tell <myname> config mod $row->module enable all");
			$off = $this->text->makeChatcmd("Off", "/tell <myname> config mod $row->module disable all");
			$blob .= strtoupper($row->module)." $a ($on/$off) $c\n";
		}
	
		$msg = $this->text->makeBlob("Module Config ($count)", $blob);
		$sendto->reply($msg);
	}

	/**
	 * This command handler turns a channel of all modules on or off.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config cmd (enable|disable) (all|guild|priv|msg)$/i")
	 */
	public function toggleChannelOfAllModulesCommand($message, $channel, $sender, $sendto, $args) {
		$status = ($args[1] == "enable" ? 1 : 0);
		$typeSql = ($args[2] == "all" ? "`type` = 'guild' OR `type` = 'priv' OR `type` = 'msg'" : "`type` = '{$args[2]}'");
	
		$sql = "SELECT type, file, cmd, admin FROM cmdcfg_<myname> WHERE `cmdevent` = 'cmd' AND ($typeSql)";
		$data = $this->db->query($sql);
		forEach ($data as $row) {
			if (!$this->accessManager->checkAccess($sender, $row->admin)) {
				continue;
			}
			if ($status == 1) {
				$this->commandManager->activate($row->type, $row->file, $row->cmd, $row->admin);
			} else {
				$this->commandManager->deactivate($row->type, $row->file, $row->cmd);
			}
		}
	
		$sql = "UPDATE cmdcfg_<myname> SET `status` = $status WHERE (`cmdevent` = 'cmd' OR `cmdevent` = 'subcmd') AND ($typeSql)";
		$this->db->exec($sql);
	
		$sendto->reply("Commands updated successfully.");
	}

	/**
	 * This command handler turns a channel of a single command, subcommand,
	 * module or event on or off.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config (subcmd|mod|cmd|event) (.+) (enable|disable) (priv|msg|guild|all)$/i")
	 */
	public function toggleChannelCommand($message, $channel, $sender, $sendto, $args) {
		if ($args[1] == "event") {
			$temp = explode(" ", $args[2]);
			$event_type = strtolower($temp[0]);
			$file = $temp[1];
		} elseif ($args[1] == 'cmd' || $args[1] == 'subcmd') {
			$cmd = strtolower($args[2]);
			$type = $args[4];
		} else { // $args[1] == 'mod'
			$module = strtoupper($args[2]);
			$type = $args[4];
		}
	
		if ($args[3] == "enable") {
			$status = 1;
		} else {
			$status = 0;
		}
	
		if ($args[1] == "mod" && $type == "all") {
			$sql = "SELECT status, type, file, cmd, admin, cmdevent FROM cmdcfg_<myname> WHERE `module` = '$module'
						UNION
					SELECT status, type, file, '' AS cmd, '' AS admin, 'event' AS cmdevent FROM eventcfg_<myname> WHERE `module` = '$module' AND `type` <> 'setup'";
		} elseif ($args[1] == "mod" && $type != "all") {
			$sql = "SELECT status, type, file, cmd, admin, cmdevent FROM cmdcfg_<myname> WHERE `module` = '$module' AND `type` = '$type'
						UNION
					SELECT status, type, file, cmd AS '', admin AS '', cmdevent AS 'event' FROM eventcfg_<myname> WHERE `module` = '$module' AND `type` = '$event_type' AND `type` <> 'setup'";
		} elseif ($args[1] == "cmd" && $type != "all") {
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = '$cmd' AND `type` = '$type' AND `cmdevent` = 'cmd'";
		} elseif ($args[1] == "cmd" && $type == "all") {
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = '$cmd' AND `cmdevent` = 'cmd'";
		} elseif ($args[1] == "subcmd" && $type != "all") {
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = '$cmd' AND `type` = '$type' AND `cmdevent` = 'subcmd'";
		} elseif ($args[1] == "subcmd" && $type == "all") {
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = '$cmd' AND `cmdevent` = 'subcmd'";
		} elseif ($args[1] == "event" && $file != "") {
			$sql = "SELECT *, 'event' AS cmdevent FROM eventcfg_<myname> WHERE `file` = '$file' AND `type` = '$event_type' AND `type` <> 'setup'";
		} else {
			$syntax_error = true;
			return;
		}
	
		$data = $this->db->query($sql);
		
		if ($args[1] == 'cmd' || $args[1] == 'subcmd') {
			if (!$this->checkCommandAccessLevels($data, $sender)) {
				$msg = "You do not have the required access level to change this command.";
				$sendto->reply($msg);
				return;
			}
		}
	
		if (count($data) == 0) {
			if ($args[1] == "mod" && $type == "all") {
				$msg = "Could not find Module <highlight>$module<end>.";
			} elseif ($args[1] == "mod" && $type != "all") {
				$msg = "Could not find module <highlight>$module<end> for channel <highlight>$type<end>.";
			} elseif ($args[1] == "cmd" && $type != "all") {
				$msg = "Could not find command <highlight>$cmd<end> for channel <highlight>$type<end>.";
			} elseif ($args[1] == "cmd" && $type == "all") {
				$msg = "Could not find command <highlight>$cmd<end>.";
			} elseif ($args[1] == "subcmd" && $type != "all") {
				$msg = "Could not find subcommand <highlight>$cmd<end> for channel <highlight>$type<end>.";
			} elseif ($args[1] == "subcmd" && $type == "all") {
				$msg = "Could not find subcommand <highlight>$cmd<end>.";
			} elseif ($args[1] == "event" && $file != "") {
				$msg = "Could not find event <highlight>$event_type<end> for handler <highlight>$file<end>.";
			}
			$sendto->reply($msg);
			return;
		}
	
		if ($args[1] == "mod" && $type == "all") {
			$msg = "Updated status of module <highlight>$module<end> to <highlight>".$args[3]."d<end>.";
		} elseif ($args[1] == "mod" && $type != "all") {
			$msg = "Updated status of module <highlight>$module<end> in channel <highlight>$type<end> to <highlight>".$args[3]."d<end>.";
		} elseif ($args[1] == "cmd" && $type != "all") {
			$msg = "Updated status of command <highlight>$cmd<end> to <highlight>".$args[3]."d<end> in channel <highlight>$type<end>.";
		} elseif ($args[1] == "cmd" && $type == "all") {
			$msg = "Updated status of command <highlight>$cmd<end> to <highlight>".$args[3]."d<end>.";
		} elseif ($args[1] == "subcmd" && $type != "all") {
			$msg = "Updated status of subcommand <highlight>$cmd<end> to <highlight>".$args[3]."d<end> in channel <highlight>$type<end>.";
		} elseif ($args[1] == "subcmd" && $type == "all") {
			$msg = "Updated status of subcommand <highlight>$cmd<end> to <highlight>".$args[3]."d<end>.";
		} elseif ($args[1] == "event" && $file != "") {
			$msg = "Updated status of event <highlight>$event_type<end> to <highlight>".$args[3]."d<end>.";
		}
	
		$sendto->reply($msg);
	
		forEach ($data as $row) {
			// only update the status if the status is different
			if ($row->status != $status) {
				if ($row->cmdevent == "event") {
					if ($status == 1) {
						$this->eventManager->activate($row->type, $row->file);
					} else {
						$this->eventManager->deactivate($row->type, $row->file);
					}
				} elseif ($row->cmdevent == "cmd") {
					if ($status == 1) {
						$this->commandManager->activate($row->type, $row->file, $row->cmd, $row->admin);
					} else {
						$this->commandManager->deactivate($row->type, $row->file, $row->cmd, $row->admin);
					}
				}
			}
		}
	
		if ($args[1] == "mod" && $type == "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `module` = ?", $status, $module);
			$this->db->exec("UPDATE eventcfg_<myname> SET `status` = ? WHERE `module` = ? AND `type` <> 'setup'", $status, $module);
		} elseif ($args[1] == "mod" && $type != "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `module` = ? AND `type` = ?", $status, $module, $type);
			$this->db->exec("UPDATE eventcfg_<myname> SET `status` = ? WHERE `module` = ? AND `type` = ? AND `type` <> 'setup'", $status, $module, $event_type);
		} elseif ($args[1] == "cmd" && $type != "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `cmd` = ? AND `type` = ? AND `cmdevent` = 'cmd'", $status, $cmd, $type);
		} elseif ($args[1] == "cmd" && $type == "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `cmd` = ? AND `cmdevent` = 'cmd'", $status, $cmd);
		} elseif ($args[1] == "subcmd" && $type != "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `cmd` = ? AND `type` = ? AND `cmdevent` = 'subcmd'", $status, $cmd, $type);
		} elseif ($args[1] == "subcmd" && $type == "all") {
			$this->db->exec("UPDATE cmdcfg_<myname> SET `status` = ? WHERE `cmd` = ? AND `cmdevent` = 'subcmd'", $status, $cmd);
		} elseif ($args[1] == "event" && $file != "") {
			$this->db->exec("UPDATE eventcfg_<myname> SET `status` = ? WHERE `type` = ? AND `file` = ? AND `type` <> 'setup'", $status, $event_type, $file);
		}
	
		// for subcommands which are handled differently
		$this->subcommandManager->loadSubcommands();
	}

	/**
	 * This command handler sets command's access level on a particular channel.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config (subcmd|cmd) (.+) admin (msg|priv|guild|all) (.+)$/i")
	 */
	public function setAccessLevelOfChannelCommand($message, $channel, $sender, $sendto, $args) {
		$category = strtolower($args[1]);
		$command = strtolower($args[2]);
		$channel = strtolower($args[3]);
		$accessLevel = $this->accessManager->getAccessLevel($args[4]);
	
		if ($category == "cmd") {
			if ($channel == "all") {
				$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = '$command' AND `cmdevent` = 'cmd'";
			} else {
				$sql = "SELECT * FROM cmdcfg_<myname> WHERE `cmd` = '$command' AND `type` = '$channel' AND `cmdevent` = 'cmd'";
			}
			$data = $this->db->query($sql);
	
			if (count($data) == 0) {
				if ($channel == "all") {
					$msg = "Could not find command <highlight>$command<end>.";
				} else {
					$msg = "Could not find command <highlight>$command<end> for channel <highlight>$channel<end>.";
				}
			} elseif (!$this->checkCommandAccessLevels($data, $sender)) {
				$msg = "You do not have the required access level to change this command.";
			} elseif (!$this->accessManager->checkAccess($sender, $accessLevel)) {
				$msg = "You may not set the access level for a command above your own access level.";
			} else {
				$this->commandManager->updateStatus($channel, $command, null, 1, $accessLevel);
		
				if ($channel == "all") {
					$msg = "Updated access of command <highlight>$command<end> to <highlight>$accessLevel<end>.";
				} else {
					$msg = "Updated access of command <highlight>$command<end> in channel <highlight>$channel<end> to <highlight>$accessLevel<end>.";
				}
			}
		} else {  // if ($category == 'subcmd')
			$sql = "SELECT * FROM cmdcfg_<myname> WHERE `type` = ? AND `cmdevent` = 'subcmd' AND `cmd` = ?";
			$data = $this->db->query($sql, $channel, $command);
			if (count($data) == 0) {
				$msg = "Could not find subcommand <highlight>$command<end> for channel <highlight>$channel<end>.";
			} elseif (!$this->checkCommandAccessLevels($data, $sender)) {
				$msg = "You do not have the required access level to change this subcommand.";
			} elseif (!$this->accessManager->checkAccess($sender, $accessLevel)) {
				$msg = "You may not set the access level for a subcommand above your own access level.";
			} else {
				$this->db->exec("UPDATE cmdcfg_<myname> SET `admin` = ? WHERE `type` = ? AND `cmdevent` = 'subcmd' AND `cmd` = ?", $accessLevel, $channel, $command);
				$this->subcommandManager->loadSubcommands();
				$msg = "Updated access of subcommand <highlight>$command<end> in channel <highlight>$channel<end> to <highlight>$accessLevel<end>.";
			}
		}
		$sendto->reply($msg);
	}
	
	public function checkCommandAccessLevels($data, $sender) {
		forEach ($data as $row) {
			if (!$this->accessManager->checkAccess($sender, $row->admin)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * This command handler shows information and controls of a command in
	 * each channel.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config cmd ([a-z0-9_]+)$/i")
	 */
	public function configCommandCommand($message, $channel, $sender, $sendto, $args) {
		$cmd = strtolower($args[1]);
		$found_msg = 0;
		$found_priv = 0;
		$found_guild = 0;
	
		$aliasCmd = $this->commandAlias->getBaseCommandForAlias($cmd);
		if ($aliasCmd != null) {
			$cmd = $aliasCmd;
		}
	
		$data = $this->db->query("SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ?", $cmd);
		if (count($data) == 0) {
			$msg = "Could not find command <highlight>$cmd<end>.";
		} else {
			$blob = '';
	
			$blob .= "<header2>Tells:<end> ";
			$blob .= $this->getCommandInfo($cmd, 'msg');
			$blob .= "\n\n";

			$blob .= "<header2>Private Channel:<end> ";
			$blob .= $this->getCommandInfo($cmd, 'priv');
			$blob .= "\n\n";
			
			$blob .= "<header2>Guild Channel:<end> ";
			$blob .= $this->getCommandInfo($cmd, 'guild');
			$blob .= "\n\n";
	
			$subcmd_list = '';
			$output = $this->getSubCommandInfo($cmd, 'msg');
			if ($output) {
				$subcmd_list .= "<header2>Available Subcommands in tells<end>\n";
				$subcmd_list .= $output;
			}
	
			$output = $this->getSubCommandInfo($cmd, 'priv');
			if ($output) {
				$subcmd_list .= "<header2>Available Subcommands in Private Channel<end>\n";
				$subcmd_list .= $output;
			}
	
			$output = $this->getSubCommandInfo($cmd, 'guild');
			if ($output) {
				$subcmd_list .= "<header2>Available Subcommands in Guild Channel<end>\n";
				$subcmd_list .= $output;
			}
	
			if ($subcmd_list) {
				$blob .= "<header>Subcommands<end>\n\n";
				$blob .= $subcmd_list;
			}
	
			$help = $this->helpManager->find($cmd, $sender);
			if ($help) {
				$blob .= "<header>Help ($cmd)<end>\n\n" . $help;
			}
	
			$msg = $this->text->makeBlob(ucfirst($cmd)." Config", $blob);
		}
		$sendto->reply($msg);
	}

	public function getAliasInfo($cmd) {
		$aliases = $this->commandAlias->findAliasesByCommand($cmd);
		$count = 0;
		forEach ($aliases as $row) {
			if ($row->status == 1) {
				$count++;
				$aliases_blob .= "{$row->alias}, ";
			}
		}

		$blob = '';
		if ($count > 0) {
			$blob .= "Aliases: <highlight>$aliases_blob<end>\n\n";
		}
		return $blob;
	}

	/**
	 * This command handler shows configuration and controls for a single module.
	 * Note: This handler has not been not registered, only activated.
	 *
	 * @Matches("/^config ([a-z0-9_]+)$/i")
	 */
	public function configModuleCommand($message, $channel, $sender, $sendto, $args) {
		$module = strtoupper($args[1]);
		$found = false;
	
		$on = $this->text->makeChatcmd("Enable", "/tell <myname> config mod {$module} enable all");
		$off = $this->text->makeChatcmd("Disable", "/tell <myname> config mod {$module} disable all");
	
		$blob = "Enable/disable entire module: ($on/$off)\n";
	
		$data = $this->db->query("SELECT * FROM settings_<myname> WHERE `module` = ? ORDER BY mode, description", $module);
		if (count($data) > 0) {
			$found = true;
			$blob .= "\n<header2>Settings<end>\n";
		}
	
		forEach ($data as $row) {
			$blob .= $row->description;
	
			if ($row->mode == "edit") {
				$blob .= " (" . $this->text->makeChatcmd("Modify", "/tell <myname> settings change $row->name") . ")";
			}
	
			$settingHandler = $this->settingManager->getSettingHandler($row);
			$blob .= ": " . $settingHandler->displayValue() . "\n";
		}
	
		$sql =
			"SELECT
				*,
				SUM(CASE WHEN type = 'guild' THEN 1 ELSE 0 END) guild_avail,
				SUM(CASE WHEN type = 'guild' AND status = 1 THEN 1 ELSE 0 END) guild_status,
				SUM(CASE WHEN type ='priv' THEN 1 ELSE 0 END) priv_avail,
				SUM(CASE WHEN type = 'priv' AND status = 1 THEN 1 ELSE 0 END) priv_status,
				SUM(CASE WHEN type ='msg' THEN 1 ELSE 0 END) msg_avail,
				SUM(CASE WHEN type = 'msg' AND status = 1 THEN 1 ELSE 0 END) msg_status
			FROM
				cmdcfg_<myname> c
			WHERE
				(`cmdevent` = 'cmd' OR `cmdevent` = 'subcmd')
				AND `module` = ?
			GROUP BY
				cmd";
		$data = $this->db->query($sql, $module);
		if (count($data) > 0) {
			$found = true;
			$blob .= "\n<header2>Commands<end>\n";
		}
		forEach ($data as $row) {
			$guild = '';
			$priv = '';
			$msg = '';
	
			if ($row->cmdevent == 'cmd') {
				$on = $this->text->makeChatcmd("ON", "/tell <myname> config cmd $row->cmd enable all");
				$off = $this->text->makeChatcmd("OFF", "/tell <myname> config cmd $row->cmd disable all");
				$cmdNameLink = $this->text->makeChatcmd($row->cmd, "/tell <myname> config cmd $row->cmd");
			} elseif ($row->cmdevent == 'subcmd') {
				$on = $this->text->makeChatcmd("ON", "/tell <myname> config subcmd $row->cmd enable all");
				$off = $this->text->makeChatcmd("OFF", "/tell <myname> config subcmd $row->cmd disable all");
				$cmdNameLink = $row->cmd;
			}
	
			if ($row->msg_avail == 0) {
				$tell = "|_";
			} elseif ($row->msg_status == 1) {
				$tell = "<green>T<end>";
			} else {
				$tell = "<red>T<end>";
			}
	
			if ($row->guild_avail == 0) {
				$guild = "|_";
			} elseif ($row->guild_status == 1) {
				$guild = "|<green>G<end>";
			} else {
				$guild = "|<red>G<end>";
			}
	
			if ($row->priv_avail == 0) {
				$priv = "|_";
			} elseif ($row->priv_status == 1) {
				$priv = "|<green>P<end>";
			} else {
				$priv = "|<red>P<end>";
			}
	
			if ($row->description != "") {
				$blob .= "$cmdNameLink ($adv$tell$guild$priv): $on  $off - ($row->description)\n";
			} else {
				$blob .= "$cmdNameLink - ($adv$tell$guild$priv): $on  $off\n";
			}
		}
	
		$data = $this->db->query("SELECT * FROM eventcfg_<myname> WHERE `type` <> 'setup' AND `module` = ?", $module);
		if (count($data) > 0) {
			$found = true;
			$blob .= "\n<header2>Events<end>\n";
		}
		forEach ($data as $row) {
			$on = $this->text->makeChatcmd("ON", "/tell <myname> config event ".$row->type." ".$row->file." enable all");
			$off = $this->text->makeChatcmd("OFF", "/tell <myname> config event ".$row->type." ".$row->file." disable all");
	
			if ($row->status == 1) {
				$status = "<green>Enabled<end>";
			} else {
				$status = "<red>Disabled<end>";
			}
	
			if ($row->description != "none") {
				$blob .= "$row->type ($row->description) - ($status): $on  $off \n";
			} else {
				$blob .= "$row->type - ($status): $on  $off \n";
			}
		}
	
		if ($found) {
			$msg = $this->text->makeBlob("$module Configuration", $blob);
		} else {
			$msg = "Could not find module <highlight>$module<end>.";
		}
		$sendto->reply($msg);
	}

	/**
	 * This helper method converts given short access level name to long name.
	 */
	private function getAdminDescription($admin) {
		$desc = $this->accessManager->getDisplayName($admin);
		return ucfirst(strtolower($desc));
	}

	/**
	 * This helper method builds information and controls for given command.
	 */
	private function getCommandInfo($cmd, $type) {
		$msg = "";
		$data = $this->db->query("SELECT * FROM cmdcfg_<myname> WHERE `cmd` = ? AND `type` = ?", $cmd, $type);
		if (count($data) == 0) {
			$msg .= "<red>Unused<end>\n";
		} elseif (count($data) == 1) {
			$row = $data[0];

			$found_msg = 1;

			$row->admin = $this->getAdminDescription($row->admin);

			if ($row->status == 1) {
				$status = "<green>Enabled<end>";
			} else {
				$status = "<red>Disabled<end>";
			}

			$msg .= "$status (Access: $row->admin) \n";
			$msg .= "Set status: ";
			$msg .= $this->text->makeChatcmd("Enabled", "/tell <myname> config cmd {$cmd} enable {$type}") . "  ";
			$msg .= $this->text->makeChatcmd("Disabled", "/tell <myname> config cmd {$cmd} disable {$type}") . "\n";

			$msg .= "Set access level: ";
			forEach ($this->accessManager->getAccessLevels() as $accessLevel => $level) {
				if ($accessLevel == 'none') {
					continue;
				}
				$msg .= $this->text->makeChatcmd(ucfirst($accessLevel), "/tell <myname> config cmd {$cmd} admin {$type} $accessLevel") . "  ";
			}
			$msg .= "\n";
		} else {
			$this->logger->log("ERROR", "Multiple rows exists for cmd: '$cmd' and type: '$type'");
		}
		return $msg;
	}

	/**
	 * This helper method builds information and controls for given subcommand.
	 */
	private function getSubCommandInfo($cmd, $type) {
		$subcmd_list = '';
		$data = $this->db->query("SELECT * FROM cmdcfg_<myname> WHERE dependson = ? AND `type` = ? AND `cmdevent` = 'subcmd'", $cmd, $type);
		forEach ($data as $row) {
			$subcmd_list .= "Command: $row->cmd\n";
			if ($row->description != "") {
				$subcmd_list .= "Description: $row->description\n";
			}

			$row->admin = $this->getAdminDescription($row->admin);

			if ($row->status == 1) {
				$status = "<green>Enabled<end>";
			} else {
				$status = "<red>Disabled<end>";
			}

			$subcmd_list .= "Current Status: $status (Access: $row->admin) \n";
			$subcmd_list .= "Set status: ";
			$subcmd_list .= $this->text->makeChatcmd("Enabled", "/tell <myname> config subcmd {$row->cmd} enable {$type}") . "  ";
			$subcmd_list .= $this->text->makeChatcmd("Disabled", "/tell <myname> config subcmd {$row->cmd} disable {$type}") . "\n";

			$subcmd_list .= "Set access level: ";
			forEach ($this->accessManager->getAccessLevels() as $accessLevel => $level) {
				if ($accessLevel == 'none') {
					continue;
				}
				$subcmd_list .= $this->text->makeChatcmd(ucfirst($accessLevel), "/tell <myname> config subcmd {$row->cmd} admin {$type} $accessLevel") . "  ";
			}
			$subcmd_list .= "\n\n";
		}
		return $subcmd_list;
	}
}
