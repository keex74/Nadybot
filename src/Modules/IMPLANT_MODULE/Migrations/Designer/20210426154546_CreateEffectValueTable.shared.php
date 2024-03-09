<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE\Migrations\Designer;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

class CreateEffectValueTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = "EffectValue";
		$db->schema()->dropIfExists($table);
		$db->schema()->create($table, function (Blueprint $table): void {
			$table->integer("EffectID")->primary();
			$table->string("Name", 50);
			$table->integer("Q200Value");
		});
	}
}
