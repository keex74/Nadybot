<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\PLAYER_LOOKUP;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\{DB, SchemaMigration};
use Psr\Log\LoggerInterface;

class IndexPlayersTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = "players";
		$db->schema()->table($table, function (Blueprint $table): void {
			$table->index(["dimension", "name"]);
			$table->index(["guild_id"]);
		});
	}
}
