<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE\Migrations\Points;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\RAID_MODULE\{RaidReward};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_05_05_10_31_03)]
class MakeRewardIdAutoIncrement implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = RaidReward::getTable();
		$db->schema()->table($table, static function (Blueprint $table): void {
			$table->dropPrimary();
		});
		$db->schema()->table($table, static function (Blueprint $table): void {
			$table->integer('id')->autoIncrement()->change();
		});
	}
}
