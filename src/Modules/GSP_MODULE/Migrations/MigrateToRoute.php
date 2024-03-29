<?php declare(strict_types=1);

namespace Nadybot\Modules\GSP_MODULE\Migrations;

use Nadybot\Core\{
	Attributes as NCA,
	DB,
	DBSchema\Setting,
	MessageHub,
	Routing\Source,
	SchemaMigration,
	SettingManager,
	SettingMode,
};
use Nadybot\Modules\GSP_MODULE\GSPController;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_08_19_07_10_37)]
class MigrateToRoute implements SchemaMigration {
	#[NCA\Inject]
	private GSPController $gspController;

	public function migrate(LoggerInterface $logger, DB $db): void {
		$channel = $this->getSetting($db, 'gsp_channels');
		if (!isset($channel)) {
			$channel = new Setting(name: 'gsp_channels', mode: SettingMode::Edit, value: '3');
		}
		$map = [
			1 => Source::PRIV . '(' . $db->getMyname() .')',
			2 => Source::ORG,
		];
		foreach ($map as $old => $new) {
			if (((int)$channel->value & $old) === 0) {
				continue;
			}
			$route = [
				'source' => $this->gspController->getChannelName(),
				'destination' => $new,
				'two_way' => false,
			];
			$db->table(MessageHub::DB_TABLE_ROUTES)->insert($route);
		}
	}

	protected function getSetting(DB $db, string $name): ?Setting {
		return $db->table(SettingManager::DB_TABLE)
			->where('name', $name)
			->asObj(Setting::class)
			->first();
	}
}
