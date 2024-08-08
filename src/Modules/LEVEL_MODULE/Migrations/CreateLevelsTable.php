<?php declare(strict_types=1);

namespace Nadybot\Modules\LEVEL_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\LEVEL_MODULE\Level;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_26_16_50_32, shared: true)]
class CreateLevelsTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = Level::getTable();
		$db->schema()->dropIfExists($table);
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->smallInteger('level')->primary();
			$table->smallInteger('teamMin');
			$table->smallInteger('teamMax');
			$table->smallInteger('pvpMin');
			$table->smallInteger('pvpMax');
			$table->integer('xpsk');
			$table->smallInteger('tokens');
			$table->smallInteger('daily_mission_xp');
			$table->text('missions');
			$table->smallInteger('max_ai_level');
			$table->smallInteger('max_le_level');
			$table->smallInteger('mob_min');
			$table->smallInteger('mob_max');
		});
	}
}
