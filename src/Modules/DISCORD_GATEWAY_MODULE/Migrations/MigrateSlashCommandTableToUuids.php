<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\DISCORD_GATEWAY_MODULE\{DiscordSlashCommand};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2024_08_01_21_18_00)]
class MigrateSlashCommandTableToUuids implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$db->migrateIdToUuid(
			DiscordSlashCommand::getTable(),
			static function (Blueprint $table): void {
				$table->uuid('id')->primary();
				$table->string('cmd', 50)->unique();
			}
		);
	}
}
