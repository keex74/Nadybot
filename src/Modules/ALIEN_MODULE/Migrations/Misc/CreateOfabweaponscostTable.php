<?php declare(strict_types=1);

namespace Nadybot\Modules\ALIEN_MODULE\Migrations\Misc;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\ALIEN_MODULE\OfabWeaponCost;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_25_13_18_27, shared: true)]
class CreateOfabweaponscostTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = OfabWeaponCost::getTable();
		if ($db->schema()->hasTable($table)) {
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->integer('ql');
			$table->integer('vp');
		});
	}
}
