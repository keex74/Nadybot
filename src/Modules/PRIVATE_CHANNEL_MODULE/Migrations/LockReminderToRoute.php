<?php declare(strict_types=1);

namespace Nadybot\Modules\PRIVATE_CHANNEL_MODULE\Migrations;

use Nadybot\Core\DBSchema\Route;
use Nadybot\Core\Routing\Source;
use Nadybot\Core\{Attributes as NCA, Config\BotConfig, DB, MessageHub, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 20_230_228_152_817)]
class LockReminderToRoute implements SchemaMigration {
	#[NCA\Inject]
	private BotConfig $config;

	public function migrate(LoggerInterface $logger, DB $db): void {
		$route = new Route(
			source: Source::SYSTEM . '(lock-reminder)',
			destination: Source::PRIV . "({$this->config->main->character})",
			two_way: false,
		);
		$route->id = $db->insert(MessageHub::DB_TABLE_ROUTES, $route);

		$route = new Route(
			source: Source::SYSTEM . '(lock-reminder)',
			destination: Source::ORG,
			two_way: false,
		);
		$route->id = $db->insert(MessageHub::DB_TABLE_ROUTES, $route);
	}
}
