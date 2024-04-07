<?php declare(strict_types=1);

namespace Nadybot\Core\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\DBSchema\CmdAlias;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_23_09_06_08)]
class CreateCmdAliasTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = CmdAlias::getTable();
		if ($db->schema()->hasTable($table)) {
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->string('cmd', 255);
			$table->string('module', 50)->nullable();
			$table->string('alias', 25)->index();
			$table->integer('status')->nullable()->default(0);
		});
	}
}
