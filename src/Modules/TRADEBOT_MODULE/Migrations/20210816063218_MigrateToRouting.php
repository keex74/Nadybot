<?php declare(strict_types=1);

namespace Nadybot\Modules\TRADEBOT_MODULE\Migrations;

use Nadybot\Core\{
	Attributes as NCA,
	Config\BotConfig,
	DB,
	DBSchema\Setting,
	MessageHub,
	Routing\Source,
	SchemaMigration,
	SettingManager,
};
use Psr\Log\LoggerInterface;

class MigrateToRouting implements SchemaMigration {
	#[NCA\Inject]
	private MessageHub $messageHub;

	#[NCA\Inject]
	private BotConfig $config;

	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = $this->messageHub::DB_TABLE_ROUTES;
		$tradebot = $this->getSetting($db, 'tradebot');
		if (!isset($tradebot) || ($tradebot->value === "None")) {
			return;
		}
		$channels = $this->getSetting($db, 'tradebot_channel_spam');
		if (!isset($channels)) {
			return;
		}
		if ((int)$channels->value & 1) {
			$route = [
				"source" => Source::TRADEBOT,
				"destination" => Source::PRIV . "({$this->config->main->character})",
				"two_way" => false,
			];
			$db->table($table)->insert($route);
		}
		if ((int)$channels->value & 2) {
			$route = [
				"source" => Source::TRADEBOT,
				"destination" => Source::ORG,
				"two_way" => false,
			];
			$db->table($table)->insert($route);
		}
	}

	protected function getSetting(DB $db, string $name): ?Setting {
		return $db->table(SettingManager::DB_TABLE)
			->where("name", $name)
			->asObj(Setting::class)
			->first();
	}
}
