<?php declare(strict_types=1);

namespace Nadybot\Modules\QUOTE_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\QUOTE_MODULE\Quote;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_27_06_39_25, shared: true)]
class CreateQuoteTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = Quote::getTable();
		if ($db->schema()->hasTable($table)) {
			$db->schema()->table($table, static function (Blueprint $table): void {
				$table->id('id')->change();
			});
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->id();
			$table->string('poster', 25);
			$table->integer('dt');
			$table->string('msg', 1_000);
		});
	}
}
