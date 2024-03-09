<?php declare(strict_types=1);

namespace Nadybot\Modules\PACKAGE_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\PACKAGE_MODULE\PackageController;
use Psr\Log\LoggerInterface;

class CreatePackageFilesTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = PackageController::DB_TABLE;
		if ($db->schema()->hasTable($table)) {
			return;
		}
		$db->schema()->create($table, function (Blueprint $table): void {
			$table->string("module", 25)->index();
			$table->string("version", 50)->index();
			$table->text("file");
		});
	}
}
