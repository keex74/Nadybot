<?php declare(strict_types=1);

namespace Nadybot\Core\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\DBSchema\{CmdPermission, CmdPermissionSet};
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2022_01_15_12_53_09)]
class CreatePermissionSet implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = CmdPermissionSet::getTable();
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->string('name', 50)->unique();
			$table->string('letter', 1)->unique();
		});

		$table = CmdPermission::getTable();
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->id();
			$table->string('permission_set', 50)->index();
			$table->string('cmd', 50)->index();
			$table->boolean('enabled')->default(false);
			$table->string('access_level', 30);
			$table->unique(['cmd', 'permission_set']);
		});
	}
}
