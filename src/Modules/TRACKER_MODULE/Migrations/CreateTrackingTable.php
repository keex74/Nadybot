<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\TRACKER_MODULE\{Tracking};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_28_06_13_08)]
class CreateTrackingTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = Tracking::getTable();
		if ($db->schema()->hasTable($table)) {
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->bigInteger('uid');
			$table->integer('dt');
			$table->string('event', 6);
		});
	}
}
