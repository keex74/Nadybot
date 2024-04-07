<?php declare(strict_types=1);

namespace Nadybot\Modules\TRACKER_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\TRACKER_MODULE\{Tracking};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_12_07_05_24_35)]
class IndexTrackingTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = Tracking::getTable();
		$db->schema()->table($table, static function (Blueprint $table): void {
			$table->bigInteger('uid')->index()->change();
		});
	}
}
