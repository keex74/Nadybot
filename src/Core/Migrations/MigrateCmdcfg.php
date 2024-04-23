<?php declare(strict_types=1);

namespace Nadybot\Core\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\DBSchema\{CmdCfg, CmdPermission, CmdPermissionSet};
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2022_01_15_13_22_57)]
class MigrateCmdcfg implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = CmdCfg::getTable();
		$db->table(CmdPermissionSet::getTable())->insert([
			['name' => 'msg',   'letter' => 'T'],
			['name' => 'priv',  'letter' => 'P'],
			['name' => 'guild', 'letter' => 'G'],
		]);
		$entries = $db->table($table)->get();
		$db->table($table)->truncate();

		$db->schema()->table($table, static function (Blueprint $table): void {
			$table->dropUnique(['cmd', 'type']);
		});
		$db->schema()->dropColumns($table, ['admin', 'status', 'type']);
		$db->schema()->table($table, static function (Blueprint $table): void {
			$table->unique('cmd');
		});

		/** @var array<string,bool> */
		$cmds = [];
		foreach ($entries as $entry) {
			$db->table(CmdPermission::getTable())->insert([
				'permission_set' => (string)$entry->type,
				'cmd' => (string)$entry->cmd,
				'enabled' => (bool)$entry->status,
				'access_level' => (string)$entry->admin,
			]);
			if (isset($cmds[(string)$entry->cmd])) {
				continue;
			}
			$db->table(CmdCfg::getTable())->insert([
				'module' => (string)$entry->module,
				'cmd' => (string)$entry->cmd,
				'cmdevent' => (string)$entry->cmdevent,
				'file' => (string)$entry->file,
				'description' => (string)$entry->description,
				'verify' => (int)$entry->verify,
				'dependson' => (string)$entry->dependson,
				'help' => !count($entry->help) ? null : (string)$entry->help,
			]);
			$cmds[(string)$entry->cmd] = true;
		}
	}
}
