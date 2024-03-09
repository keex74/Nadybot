<?php declare(strict_types=1);

namespace Nadybot\Modules\RELAY_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\RELAY_MODULE\RelayController;
use Psr\Log\LoggerInterface;

class CreateRelayLayerTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = RelayController::DB_TABLE_LAYER;
		$db->schema()->create($table, function (Blueprint $table): void {
			$table->id();
			$table->unsignedBigInteger("relay_id")->index();
			$table->string("layer", 100);
		});
	}
}
