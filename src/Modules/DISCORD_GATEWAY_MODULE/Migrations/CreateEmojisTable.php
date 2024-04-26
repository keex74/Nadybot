<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\DISCORD_GATEWAY_MODULE\{DBEmoji};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2023_04_06_13_58_55)]
class CreateEmojisTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = DBEmoji::getTable();
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->id();
			$table->string('name', 20)->index();
			$table->unsignedInteger('registered');
			$table->unsignedInteger('version');
			$table->string('emoji_id', 24);
			$table->string('guild_id', 24);
		});
	}
}
