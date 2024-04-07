<?php declare(strict_types=1);

namespace Nadybot\Modules\PRIVATE_CHANNEL_MODULE\Migrations;

use Nadybot\Core\DBSchema\RouteHopColor;
use Nadybot\Core\{
	Attributes as NCA,
	Config\BotConfig,
	DB,
	DBSchema\Setting,
	Routing\Source,
	Safe,
	SchemaMigration,
};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_08_12_17_36_58)]
class MoveSettingsToHopColors implements SchemaMigration {
	#[NCA\Inject]
	private BotConfig $config;

	public function migrate(LoggerInterface $logger, DB $db): void {
		$hop = [
			'tag_color' => $this->getSettingColor($db, 'guest_color_channel') ?? 'C3C3C3',
			'text_color' => $this->getSettingColor($db, 'guest_color_guild') ?? 'C3C3C3',
			'hop' => Source::PRIV . '(' . $this->config->main->character . ')',
		];
		$db->table(RouteHopColor::getTable())->insert($hop);

		if (strlen($this->config->general->orgName)) {
			$hop = [
				'tag_color' => $this->getSettingColor($db, 'guest_color_channel') ?? 'C3C3C3',
				'text_color' => $this->getSettingColor($db, 'guest_color_guest') ?? 'C3C3C3',
				'hop' => Source::ORG . "({$this->config->general->orgName})",
			];
			$db->table(RouteHopColor::getTable())->insert($hop);
		}
	}

	protected function getSettingColor(DB $db, string $name): ?string {
		/** @var ?Setting */
		$setting = $db->table(Setting::getTable())
			->where('name', $name)
			->asObj(Setting::class)
			->first();
		if (!isset($setting) || ($setting->value??'') === '') {
			return null;
		}
		if (count($matches = Safe::pregMatch('/#([a-f0-9]{6})/i', $setting->value??'')) === 2) {
			return $matches[1];
		}
		return null;
	}
}
