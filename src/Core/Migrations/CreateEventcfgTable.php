<?php declare(strict_types=1);

namespace Nadybot\Core\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\DBSchema\EventCfg;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_23_08_32_43)]
class CreateEventcfgTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = EventCfg::getTable();
		if ($db->schema()->hasTable($table)) {
			$db->schema()->table($table, static function (Blueprint $table): void {
				$table->string('type', 50)->nullable()->change();
				$table->string('file', 100)->nullable()->change();
				$table->integer('verify')->nullable()->default(0)->index()->change();
			});
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->string('module', 50)->nullable()->index();
			$table->string('type', 50)->nullable()->index();
			$table->string('file', 100)->nullable()->index();
			$table->string('description', 75)->nullable()->default('none');
			$table->integer('verify')->nullable()->default(0)->index();
			$table->integer('status')->nullable()->default(0);
			$table->string('help', 255)->nullable();
		});
	}
}
