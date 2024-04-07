<?php declare(strict_types=1);

namespace Nadybot\Modules\TRADEBOT_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\TRADEBOT_MODULE\{TradebotColors};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_28_08_21_30)]
class CreateTradebotColorsTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = TradebotColors::getTable();
		if ($db->schema()->hasTable($table)) {
			$db->schema()->table($table, static function (Blueprint $table): void {
				$table->id('id')->change();
			});
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->id();
			$table->string('tradebot', 12)->index();
			$table->string('channel', 25)->default('*')->index();
			$table->string('color', 6);
			$table->unique(['tradebot', 'channel']);
		});
	}
}
