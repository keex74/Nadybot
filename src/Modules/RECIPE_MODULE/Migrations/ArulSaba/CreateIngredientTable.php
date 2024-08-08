<?php declare(strict_types=1);

namespace Nadybot\Modules\RECIPE_MODULE\Migrations\ArulSaba;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\RECIPE_MODULE\Ingredient;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_27_13_45_04, shared: true)]
class CreateIngredientTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = Ingredient::getTable();
		$db->schema()->dropIfExists($table);
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->integer('id')->primary();
			$table->string('name', 50);
			$table->integer('aoid')->nullable()->index();
			$table->text('where')->nullable();
		});
	}
}
