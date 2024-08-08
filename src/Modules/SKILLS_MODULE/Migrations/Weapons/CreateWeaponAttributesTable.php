<?php declare(strict_types=1);

namespace Nadybot\Modules\SKILLS_MODULE\Migrations\Weapons;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\SKILLS_MODULE\WeaponAttribute;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_12_07_10_46_01, shared: true)]
class CreateWeaponAttributesTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = WeaponAttribute::getTable();
		$db->schema()->dropIfExists($table);
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->integer('id')->primary();
			$table->integer('attack_time');
			$table->integer('recharge_time');
			$table->integer('full_auto')->nullable();
			$table->integer('burst')->nullable();
			$table->tinyInteger('fling_shot');
			$table->tinyInteger('fast_attack');
			$table->tinyInteger('aimed_shot');
			$table->tinyInteger('brawl');
			$table->tinyInteger('sneak_attack');
			$table->integer('multi_m')->nullable();
			$table->integer('multi_r')->nullable();
		});
	}
}
