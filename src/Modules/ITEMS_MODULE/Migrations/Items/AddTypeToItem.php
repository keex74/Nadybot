<?php declare(strict_types=1);

namespace Nadybot\Modules\ITEMS_MODULE\Migrations\Items;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\ITEMS_MODULE\AODBEntry;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2023_01_08_11_27_14, shared: true)]
class AddTypeToItem implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$db->table(AODBEntry::getTable())->truncate();
		$db->schema()->table(AODBEntry::getTable(), static function (Blueprint $table): void {
			$table->string('type')->nullable(false);
		});
	}
}
