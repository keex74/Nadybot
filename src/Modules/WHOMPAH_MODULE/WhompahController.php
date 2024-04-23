<?php declare(strict_types=1);

namespace Nadybot\Modules\WHOMPAH_MODULE;

use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	DB,
	ModuleInstance,
	ParamClass\PWord,
	Text,
	Types\Faction,
};

/**
 * @author Tyrence (RK2)
 */
#[
	NCA\Instance,
	NCA\HasMigrations,
	NCA\DefineCommand(
		command: 'whompah',
		accessLevel: 'guest',
		description: 'Shows the whompah route from one city to another',
		alias: ['whompahs', 'whompa', 'whompas'],
	)
]
class WhompahController extends ModuleInstance {
	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Setup]
	public function setup(): void {
		$this->db->loadCSVFile($this->moduleName, __DIR__ . '/whompah_cities.csv');
		$this->db->loadCSVFile($this->moduleName, __DIR__ . '/whompah_cities_rel.csv');
	}

	/** Shows a list of whompah cities */
	#[NCA\HandlesCommand('whompah')]
	public function whompahListCommand(CmdContext $context): void {
		$data = $this->db->table(WhompahCity::getTable())
			->orderBy('city_name')
			->asObj(WhompahCity::class);

		$blob = "<header2>All known cities with Whom-Pahs<end>\n";
		foreach ($data as $row) {
			$cityLink = Text::makeChatcmd($row->short_name, "/tell <myname> whompah {$row->short_name}");
			$blob .= "<tab>{$row->city_name} ({$cityLink})\n";
		}
		$blob .= "\nWritten By Tyrence (RK2)\nDatabase from a Bebot module written by POD13";

		$msg = $this->text->makeBlob('Whompah Cities', $blob);

		$context->reply($msg);
	}

	/** Searches a whompah-route from one location to another */
	#[NCA\HandlesCommand('whompah')]
	public function whompahTravelCommand(CmdContext $context, PWord $start, PWord $end): void {
		$startCity = $this->findCity($start());
		$endCity   = $this->findCity($end());

		if ($startCity === null) {
			$msg = "Error! Could not find city <highlight>{$start}<end>.";
			$context->reply($msg);
			return;
		}
		if ($endCity === null) {
			$msg = "Error! Could not find city <highlight>{$end}<end>.";
			$context->reply($msg);
			return;
		}

		$whompahs = $this->buildWhompahNetwork();

		$whompah = new WhompahPath(current: $endCity, visited: true);
		$obj = $this->findWhompahPath([$whompah], $whompahs, $startCity->id);

		if ($obj === null) {
			$msg = 'There was an error while trying to find the whompah path.';
			$context->reply($msg);
			return;
		}
		$cities = [];
		while ($obj !== null) {
			$cities []= $obj->current;
			$obj = $obj->previous;
		}
		$cityList = $this->getColoredNamelist($cities);
		$msg = implode(' -> ', $cityList);

		$context->reply($msg);
	}

	/** Show all whompah-connections of a city */
	#[NCA\HandlesCommand('whompah')]
	public function whompahDestinationsCommand(CmdContext $context, string $cityName): void {
		$city = $this->findCity($cityName);

		if ($city === null) {
			$msg = "Error! Could not find city <highlight>{$cityName}<end>.";
			$context->reply($msg);
			return;
		}

		$cities = $this->db->table(WhompahCityRel::getTable(), 'w1')
			->join(WhompahCity::getTable(as: 'w2'), 'w1.city2_id', 'w2.id')
			->where('w1.city1_id', $city->id)
			->orderBy('w2.city_name')
			->select('w2.*')
			->asObjArr(WhompahCity::class);

		$msg = "From <highlight>{$city->city_name}<end> ({$city->short_name}) you can get to\n- " .
			implode("\n- ", $this->getColoredNamelist($cities, true));

		$context->reply($msg);
	}

	/**
	 * @param list<WhompahPath>      $queue
	 * @param array<int,WhompahPath> $whompahs
	 *
	 * @return ?WhompahPath
	 */
	public function findWhompahPath(array $queue, array $whompahs, int $endCity): ?WhompahPath {
		$currentWhompah = array_shift($queue);

		if ($currentWhompah === null) {
			return null;
		}

		if ($currentWhompah->current->id === $endCity) {
			return $currentWhompah;
		}

		foreach ($whompahs[$currentWhompah->current->id]->connections as $city2Id) {
			if ($whompahs[$city2Id]->visited !== true) {
				$whompahs[$city2Id]->visited = true;
				$nextWhompah = clone $whompahs[$city2Id];
				$nextWhompah->previous = $currentWhompah;
				$queue []= $nextWhompah;
			}
		}

		return $this->findWhompahPath($queue, $whompahs, $endCity);
	}

	public function findCity(string $search): ?WhompahCity {
		$q1 = $this->db->table(WhompahCity::getTable())->whereIlike('city_name', $search)
			->orWhereIlike('short_name', $search);
		$q2 = $this->db->table(WhompahCity::getTable())->whereIlike('city_name', "%{$search}%")
			->orWhereIlike('short_name', "%{$search}%");
		return $q1->asObj(WhompahCity::class)->first()
			?? $q2->asObj(WhompahCity::class)->first();
	}

	/** @return array<int,WhompahPath> */
	public function buildWhompahNetwork(): array {
		/** @var array<int,WhompahCity> */
		$cities = $this->db->table(WhompahCity::getTable())
			->asObj(WhompahCity::class)
			->keyBy('id')
			->toArray();

		/** @var array<int,WhompahPath> */
		$network = [];
		foreach ($cities as $id => $city) {
			$network[$id] = new WhompahPath(current: $city);
		}

		$this->db->table(WhompahCityRel::getTable())->orderBy('city1_id')
			->asObj(WhompahCityRel::class)
			->each(static function (WhompahCityRel $city) use ($network) {
				$network[$city->city1_id]->connections []= $city->city2_id;
			});

		return $network;
	}

	/**
	 * @param list<WhompahCity> $cities
	 *
	 * @return list<string>
	 */
	protected function getColoredNamelist(array $cities, bool $addShort=false): array {
		return array_map(static function (WhompahCity $city) use ($addShort): string {
			$faction = strtolower($city->faction->value);
			if ($city->faction === Faction::Neutral) {
				$faction = 'green';
			}
			$coloredName = "<{$faction}>{$city->city_name}<end>";
			if ($addShort) {
				$coloredName .= " ({$city->short_name})";
			}
			return $coloredName;
		}, $cities);
	}
}
