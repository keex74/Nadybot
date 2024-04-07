<?php declare(strict_types=1);

namespace Nadybot\Core\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\DBSchema\HlpCfg;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_23_08_56_47)]
class CreateHlpcfgTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = HlpCfg::getTable();
		if ($db->schema()->hasTable($table)) {
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->string('name', 25)->index();
			$table->string('module', 50)->nullable();
			$table->string('file', 255)->nullable();
			$table->string('description', 75)->nullable();
			$table->string('admin', 10)->nullable();
			$table->integer('verify')->nullable()->default(0);
		});
	}
}
