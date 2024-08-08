<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\ALTS\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\DBSchema\Alt;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_11_09_13_50_26, shared: true)]
class MakeMainNotNull implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = Alt::getTable();
		$db->schema()->table($table, static function (Blueprint $table): void {
			$table->string('main', 25)->nullable(false)->change();
		});
	}
}
