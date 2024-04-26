<?php declare(strict_types=1);

namespace Nadybot\Modules\WHEREIS_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2022_06_29_10_05_42, shared: true)]
class CreateSpawntimeTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = 'spawntime';
		$db->schema()->dropIfExists($table);
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->string('mob', 50)->primary();
			$table->string('alias', 50)->nullable(true)->index();
			$table->string('placeholder', 50)->nullable(true)->index();
			$table->boolean('can_skip_spawn')->nullable(true);
			$table->integer('spawntime')->nullable(true);
		});
	}
}
