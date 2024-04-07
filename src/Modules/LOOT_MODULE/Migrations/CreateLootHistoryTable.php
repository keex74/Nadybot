<?php declare(strict_types=1);

namespace Nadybot\Modules\LOOT_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\LOOT_MODULE\LootHistory;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2022_05_05_15_01_53)]
class CreateLootHistoryTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = LootHistory::getTable();
		$db->schema()->create($table, static function (Blueprint $table) {
			$table->id();
			$table->unsignedInteger('dt')->index();
			$table->unsignedInteger('roll')->index();
			$table->unsignedInteger('pos');
			$table->unsignedInteger('amount');
			$table->string('name', 200);
			$table->unsignedInteger('icon')->nullable();
			$table->string('added_by', 12)->index();
			$table->string('rolled_by', 12);
			$table->string('display', 200);
			$table->string('comment', 200);
			$table->string('winner', 12)->nullable(true)->index();
		});
	}
}
