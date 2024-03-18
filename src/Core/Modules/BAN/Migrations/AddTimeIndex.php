<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\BAN\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 20221129080210, shared: true)]
class AddTimeIndex implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = "name_history";
		$db->schema()->table($table, function (Blueprint $table) {
			$table->index("dt");
		});
	}
}