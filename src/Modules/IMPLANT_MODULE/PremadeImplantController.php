<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE;

use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	DB,
	ImplantSlot,
	ModuleInstance,
	Profession,
	QueryBuilder,
	Text,
};
use Nadybot\Modules\ITEMS_MODULE\{
	Skill,
	WhatBuffsController,
};

/**
 * @author Tyrence (RK2)
 * Commands this class contains:
 */
#[
	NCA\Instance,
	NCA\HasMigrations('Migrations/Premade'),
	NCA\DefineCommand(
		command: 'premade',
		accessLevel: 'guest',
		description: 'Searches for implants out of the premade implants booths',
	)
]
class PremadeImplantController extends ModuleInstance {
	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private WhatBuffsController $whatBuffsController;

	#[NCA\Setup]
	public function setup(): void {
		$this->db->loadCSVFile($this->moduleName, __DIR__ . '/premade_implant.csv');
	}

	/** Search for implants by profession, slot, or modifier in the premade implant booth */
	#[NCA\HandlesCommand('premade')]
	#[NCA\Help\Example('<symbol>premade agent')]
	#[NCA\Help\Example('<symbol>premade cl')]
	#[NCA\Help\Example('<symbol>premade rwrist')]
	public function premadeCommand(CmdContext $context, string $search): void {
		$searchTerms = strtolower($search);
		$results = null;

		$profession = Profession::tryByName($searchTerms);
		if (isset($profession)) {
			$searchTerms = $profession->value;
			$results = $this->searchByProfession($profession);
		} elseif (PImplantSlot::matches($searchTerms)) {
			$results = $this->searchBySlot((new PImplantSlot($searchTerms))());
		} else {
			$results = $this->searchByModifier($searchTerms);
		}

		if (!empty($results)) {
			$blob = trim($this->formatResults($results));
			$msg = $this->text->makeBlob("Implant Search Results for '{$searchTerms}'", $blob);
		} else {
			$msg = 'No results found.';
		}

		$context->reply($msg);
	}

	/** @return PremadeSearchResult[] */
	public function searchByProfession(Profession $profession): array {
		$query = $this->getBaseQuery()->where('p2.Name', $profession->value);
		return $query->asObj(PremadeSearchResult::class)->toArray();
	}

	/** @return PremadeSearchResult[] */
	public function searchBySlot(ImplantSlot $slot): array {
		$query = $this->getBaseQuery()->where('i.ShortName', $slot->designSlotName());
		return $query->asObj(PremadeSearchResult::class)->toArray();
	}

	/** @return PremadeSearchResult[] */
	public function searchByModifier(string $modifier): array {
		$skills = $this->whatBuffsController->searchForSkill($modifier);
		if (!count($skills)) {
			return [];
		}
		$skillIds = array_map(
			static function (Skill $s): int {
				return $s->id;
			},
			$skills
		);
		$query = $this->getBaseQuery()
			->whereIn('c1.SkillID', $skillIds)
			->orWhereIn('c2.SkillID', $skillIds)
			->orWhereIn('c3.SkillID', $skillIds);

		return $query->asObj(PremadeSearchResult::class)->toArray();
	}

	/** @param PremadeSearchResult[] $implants */
	public function formatResults(array $implants): string {
		$blob = '';
		$slotMap = [];
		foreach ($implants as $implant) {
			$slotMap[$implant->slot] ??= [];
			$slotMap[$implant->slot] []= $implant;
		}
		foreach ($slotMap as $slot => $implants) {
			$blob .= "<header2>{$slot}<end>\n";
			foreach ($implants as $implant) {
				$blob .= $this->getFormattedLine($implant);
			}
		}

		return $blob;
	}

	public function getFormattedLine(PremadeSearchResult $implant): string {
		return "<tab><highlight>{$implant->profession->name}<end> ({$implant->ability})\n".
			"<tab>S: {$implant->shiny}\n".
			"<tab>B: {$implant->bright}\n".
			"<tab>F: {$implant->faded}\n\n";
	}

	protected function getBaseQuery(): QueryBuilder {
		$query = $this->db->table(PremadeImplant::getTable(), 'p')
			->join(ImplantType::getTable(as: 'i'), 'p.ImplantTypeID', 'i.ImplantTypeID')
			->join('Profession AS p2', 'p.ProfessionID', 'p2.ID')
			->join(Ability::getTable(as: 'a'), 'p.AbilityID', 'a.AbilityID')
			->join(Cluster::getTable(as: 'c1'), 'p.ShinyClusterID', 'c1.ClusterID')
			->join(Cluster::getTable(as: 'c2'), 'p.BrightClusterID', 'c2.ClusterID')
			->join(Cluster::getTable(as: 'c3'), 'p.FadedClusterID', 'c3.ClusterID')
			->orderBy('slot')
			->select(['i.Name AS slot', 'p2.Name AS profession', 'a.Name as ability']);
		$query->selectRaw(
			'CASE WHEN ' . $query->grammar->wrap('c1.ClusterID') . ' = 0 '.
			'THEN ? '.
			'ELSE ' .$query->grammar->wrap('c1.LongName'). ' '.
			'END AS ' . $query->grammar->wrap('shiny')
		)->addBinding('N/A', 'select');
		$query->selectRaw(
			'CASE WHEN ' . $query->grammar->wrap('c2.ClusterID') . ' = 0 '.
			'THEN ? '.
			'ELSE ' .$query->grammar->wrap('c2.LongName'). ' '.
			'END AS ' . $query->grammar->wrap('bright')
		)->addBinding('N/A', 'select');
		$query->selectRaw(
			'CASE WHEN ' . $query->grammar->wrap('c3.ClusterID') . ' = 0 '.
			'THEN ? '.
			'ELSE ' .$query->grammar->wrap('c3.LongName'). ' '.
			'END AS ' . $query->grammar->wrap('faded')
		)->addBinding('N/A', 'select');
		return $query;
	}
}
