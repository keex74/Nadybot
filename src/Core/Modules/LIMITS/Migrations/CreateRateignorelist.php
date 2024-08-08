<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\LIMITS\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\DBSchema\RateIgnoreList;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_24_21_17_50, shared: true)]
class CreateRateignorelist implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = RateIgnoreList::getTable();
		if ($db->schema()->hasTable($table)) {
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->string('name', 20);
			$table->string('added_by', 20);
			$table->integer('added_dt');
		});
	}
}
