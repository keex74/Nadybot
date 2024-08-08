<?php declare(strict_types=1);

namespace Nadybot\Modules\RECIPE_MODULE\Migrations\ArulSaba;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\RECIPE_MODULE\ArulSabaBuffs;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_27_13_25_01, shared: true)]
class CreateArulsabaBuffsTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = ArulSabaBuffs::getTable();
		if ($db->schema()->hasTable($table)) {
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->string('name', 20)->index();
			$table->integer('min_level');
			$table->integer('left_aoid');
			$table->integer('right_aoid');
			$table->unique(['name', 'min_level']);
		});
	}
}
