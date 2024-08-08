<?php declare(strict_types=1);

namespace Nadybot\Modules\LOOT_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\LOOT_MODULE\RaidLoot;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_26_16_55_59, shared: true)]
class CreateRaidLootTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = RaidLoot::getTable();
		$db->schema()->dropIfExists($table);
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->integer('id')->primary();
			$table->string('raid', 30)->index();
			$table->string('category', 50)->index();
			$table->integer('ql');
			$table->string('name', 255);
			$table->string('comment', 255);
			$table->integer('multiloot');
			$table->integer('aoid')->nullable();
		});
	}
}
