<?php declare(strict_types=1);

namespace Nadybot\Modules\RAID_MODULE\Migrations\Raid;

use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{
	DB,
	DBSchema\Setting,
	MessageHub,
	Routing\Source,
	SchemaMigration,
	SettingManager,
};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 20220329160122)]
class MigrateToRoutes implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$route = [
			"source" => "raid(*)",
			"destination" => Source::PRIV . "(" . $db->getMyname() . ")",
			"two_way" => false,
		];
		$db->table(MessageHub::DB_TABLE_ROUTES)->insert($route);

		$format = [
			"render" => false,
			"hop" => 'raid',
			"format" => '%s',
		];
		$db->table(Source::DB_TABLE)->insert($format);

		$raidAnnounceRaidmemberLoc = $this->getSetting($db, 'raid_announce_raidmember_loc');
		if (!isset($raidAnnounceRaidmemberLoc)) {
			return;
		}
		$raidInformMemberBeingAdded = ((int)($raidAnnounceRaidmemberLoc->value??3) & 2) === 2;
		$db->table(SettingManager::DB_TABLE)
			->where("name", $raidAnnounceRaidmemberLoc->name)
			->update([
				"name" => "raid_inform_member_being_added",
				"value" => $raidInformMemberBeingAdded ? "1" : "0",
				"type" => 'bool',
			]);
	}

	protected function getSetting(DB $db, string $name): ?Setting {
		return $db->table(SettingManager::DB_TABLE)
			->where("name", $name)
			->asObj(Setting::class)
			->first();
	}
}
