<?php declare(strict_types=1);

namespace Nadybot\Core;

use function Amp\async;

use Nadybot\Core\{
	Attributes as NCA,
	Config\BotConfig,
	DBSchema\Admin,
	DBSchema\Audit,
};

/**
 * Manage the bot admins
 */
#[NCA\Instance]
class AdminManager implements AccessLevelProvider {
	public const DB_TABLE = "admin_<myname>";

	/**
	 * Admin access levels of our admin users
	 *
	 * @var array<string,array<string,int>>
	 */
	public array $admins = [];

	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private BuddylistManager $buddylistManager;

	#[NCA\Inject]
	private AccessManager $accessManager;

	#[NCA\Inject]
	private BotConfig $config;

	public function getSingleAccessLevel(string $sender): ?string {
		$level = $this->admins[$sender]["level"] ?? 0;
		if ($level >= 4) {
			return "admin";
		} elseif ($level >= 3) {
			return "mod";
		}
		return null;
	}

	#[NCA\Setup]
	public function setup(): void {
		$this->accessManager->registerProvider($this);
	}

	/** Load the bot admins from database into $admins */
	public function uploadAdmins(): void {
		foreach ($this->config->general->superAdmins as $superAdmin) {
			$this->db->table(self::DB_TABLE)->upsert(
				[
					"adminlevel" => 4,
					"name" => $superAdmin,
				],
				"name"
			);
		}

		$this->db->table(self::DB_TABLE)
			->asObj(Admin::class)
			->each(function (Admin $row): void {
				if (isset($row->adminlevel)) {
					$this->admins[$row->name] = ["level" => $row->adminlevel];
				}
			});
	}

	/** Demote someone from the admin position */
	public function removeFromLists(string $who, string $sender): void {
		$oldRank = $this->admins[$who]??[];
		unset($this->admins[$who]);
		$this->db->table(self::DB_TABLE)->where("name", $who)->delete();
		$this->buddylistManager->remove($who, 'admin');
		$alMod = $this->accessManager->getAccessLevels()["mod"];
		$audit = new Audit(
			actor: $sender,
			actee: $who,
			action: AccessManager::DEL_RANK,
			value: (string)($alMod - ($oldRank["level"] - $alMod)),
		);
		$this->accessManager->addAudit($audit);
	}

	/**
	 * Set the admin level of a user
	 *
	 * @return string Either "demoted" or "promoted"
	 */
	public function addToLists(string $who, int $intlevel, string $sender): string {
		$action = 'promoted';
		$alMod = $this->accessManager->getAccessLevels()["mod"];
		if (isset($this->admins[$who])) {
			$this->db->table(self::DB_TABLE)
				->where("name", $who)
				->update(["adminlevel" => $intlevel]);
			if ($this->admins[$who]["level"] > $intlevel) {
				$action = "demoted";
			}
			$audit = new Audit(
				actor: $sender,
				actee: $who,
				action: AccessManager::DEL_RANK,
				value: (string)($alMod - ($this->admins[$who]["level"] - $alMod)),
			);
			$this->accessManager->addAudit($audit);
		} else {
			$this->db->table(self::DB_TABLE)
				->insert(["adminlevel" => $intlevel, "name" => $who]);
		}

		$this->admins[$who]["level"] = $intlevel;
		async($this->buddylistManager->addName(...), $who, 'admin')->ignore();

		$audit = new Audit(
			actor: $sender,
			actee: $who,
			action: AccessManager::ADD_RANK,
			value: (string)($alMod - ($intlevel - $alMod)),
		);
		$this->accessManager->addAudit($audit);

		return $action;
	}

	/** Check if a user $who has admin level $level */
	public function checkExisting(string $who, int $level): bool {
		if ($this->admins[$who]["level"] !== $level) {
			return false;
		}
		return true;
	}
}
