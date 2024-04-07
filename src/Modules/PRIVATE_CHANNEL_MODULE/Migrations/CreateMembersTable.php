<?php declare(strict_types=1);

namespace Nadybot\Modules\PRIVATE_CHANNEL_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\DBSchema\Member;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_27_06_23_04)]
class CreateMembersTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = Member::getTable();
		if ($db->schema()->hasTable($table)) {
			return;
		}
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->string('name', 25)->primary();
			$table->integer('autoinv')->nullable()->default(0);
		});
	}
}
