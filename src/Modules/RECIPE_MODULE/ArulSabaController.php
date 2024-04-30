<?php declare(strict_types=1);

namespace Nadybot\Modules\RECIPE_MODULE;

use Exception;
use Nadybot\Core\Types\ItemFlag;
use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	DB,
	ModuleInstance,
	ParamClass\PWord,
	Safe,
	Text,
};
use Nadybot\Modules\ITEMS_MODULE\{
	AODBItem,
	ItemWithBuffs,
	ItemsController,
	Skill,
};

/**
 * @author Nadyita
 */
#[
	NCA\Instance,
	NCA\HasMigrations('Migrations/ArulSaba'),
	NCA\DefineCommand(
		command: 'arulsaba',
		accessLevel: 'guest',
		description: 'Get recipe for Arul Saba bracers',
		alias: 'aruls'
	)
]
class ArulSabaController extends ModuleInstance {
	public const ME = 125;
	public const EE = 126;
	public const AGI = 17;

	/** Show images for the Arul Saba steps */
	#[NCA\Setting\Options(options: [
		'yes, with links' => 2,
		'yes' => 1,
		'no' => 0,
	])]
	public int $arulsabaShowImages = 2;

	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private ItemsController $itemsController;

	#[NCA\Setup]
	public function setup(): void {
		$this->db->loadCSVFile($this->moduleName, __DIR__ . '/arulsaba.csv');
		$this->db->loadCSVFile($this->moduleName, __DIR__ . '/arulsaba_buffs.csv');
		$this->db->loadCSVFile($this->moduleName, __DIR__ . '/ingredient.csv');
	}

	/** Get a list of all Arul Saba bracelets */
	#[NCA\HandlesCommand('arulsaba')]
	public function arulSabaListCommand(CmdContext $context): void {
		$blob = "<header2>Choose the type of bracer<end>\n";
		$blob = $this->db->table(ArulSaba::getTable())
			->asObj(ArulSaba::class)
			->reduce(static function (string $blob, ArulSaba $type): string {
				$chooseLink = Text::makeChatcmd(
					'Choose QL',
					"/tell <myname> arulsaba {$type->name}"
				);
				return "{$blob}<tab>[{$chooseLink}] ".
					"{$type->lesser_prefix}/{$type->regular_prefix} ".
					"{$type->name}: <highlight>{$type->buffs}<end>\n";
			}, $blob);
		$msg = $this->text->makeBlob('Arul Saba - Choose type', $blob);
		$context->reply($msg);
	}

	/** See the different types of a specific Arul Saba bracelet */
	#[NCA\HandlesCommand('arulsaba')]
	#[NCA\Help\Example('<symbol>arulsaba desert')]
	public function arulSabaChooseQLCommand(CmdContext $context, PWord $name): void {
		$aruls = $this->db->table(ArulSabaBuffs::getTable())
			->where('name', ucfirst(strtolower($name())))
			->orderBy('min_level')
			->asObj(ArulSabaBuffs::class);
		if ($aruls->isEmpty()) {
			$context->reply("No Bracelet of Arul Saba ({$name}) found.");
			return;
		}
		$blob = '';
		$gems = 0;
		foreach ($aruls as $arul) {
			$item = $this->itemsController->findById($arul->left_aoid);
			if (!isset($item)) {
				$context->reply("Cannot find item #{$arul->left_aoid} in bot's item database.");
				return;
			}

			/** @var ItemWithBuffs */
			$item = $this->itemsController->addBuffs($item)->firstOrFail();
			$shortName = Safe::pregReplace("/^.*\((.+?) - Left\)$/", '$1', $item->name);
			$blob .= "<header2>{$shortName}<end>\n".
				"<tab>Min level: <highlight>{$arul->min_level}<end>\n";
			foreach ($item->buffs as $buff) {
				$blob .= "<tab>{$buff->skill->name}: <highlight>+{$buff->amount}{$buff->skill->unit}<end>\n";
			}
			$leftLink = Text::makeChatcmd('Left', "/tell <myname> arulsaba {$arul->name} {$gems} left");
			$rightLink = Text::makeChatcmd('Right', "/tell <myname> arulsaba {$arul->name} {$gems} right");
			$blob .= "<tab>Recipe: [{$leftLink}] [{$rightLink}]\n\n";
			$gems++;
		}
		$msg = $this->text->makeBlob("Types of a Arul Saba {$aruls[0]->name} bracelet", $blob);
		$context->reply($msg);
	}

	public function readIngredientByAoid(int $aoid, int $amount=1, ?int $ql=null, bool $qlCanBeHigher=false): Ingredient {
		/** @var Ingredient|null */
		$ing = $this->db->table(Ingredient::getTable())
			->where('aoid', $aoid)
			->asObj(Ingredient::class)
			->first();
		if (!isset($ing)) {
			throw new Exception("Cannot find ingredient #{$aoid} in the bot's database.");
		}
		return $this->enrichIngredient($ing, $amount, $ql, $qlCanBeHigher);
	}

	public function readIngredientByName(string $name, int $amount=1, ?int $ql=null, bool $qlCanBeHigher=false): Ingredient {
		/** @var Ingredient|null */
		$ing = $this->db->table(Ingredient::getTable())
			->where('name', $name)
			->asObj(Ingredient::class)
			->first();
		if (!isset($ing)) {
			$query = $this->db->table(Ingredient::getTable());
			$tmp = explode(' ', $name);
			$this->db->addWhereFromParams($query, $tmp, 'name');
			$ing = $query->asObj(Ingredient::class)->first();
		}
		if (!isset($ing)) {
			throw new Exception("Cannot find ingredient {$name} in the bot's database.");
		}
		return $this->enrichIngredient($ing, $amount, $ql, $qlCanBeHigher);
	}

	/** See the recipe for a specific Arul Sabe bracelet */
	#[NCA\HandlesCommand('arulsaba')]
	#[NCA\Help\Example('<symbol>arulsaba desert 5 left')]
	public function arulSabaRecipeCommand(
		CmdContext $context,
		PWord $type,
		int $numGems,
		#[NCA\StrChoice('left', 'right')] string $side
	): void {
		$type = ucfirst(strtolower($type()));

		/** @var int */
		$reqGems = max(1, $numGems);
		$side = strtolower($side);

		$gemGrades = [
			['Arbiter Gem',     'Scheol',   288,  306],
			['Monarch Gem',     'Adonis',   528,  563],
			['Emperor Gem',     'Penumbra', 937, 1_035],
			['Stellar Jewel',   'Inferno', 1_665, 1_775],
			['Galactic Jewel', 'Alappaa', 2_100, 2_270],
		];
		$blueprints = [
			[150_871, 150_870,  80, 150_862, 150_866, 150_857, 150_861],
			[150_871, 150_870,  80, 150_862, 150_866, 150_857, 150_861],
			[150_870, 150_869, 110, 150_866, 150_865, 150_861, 150_859],
			[150_869, 150_867, 150, 150_865, 150_863, 150_859, 150_858],
			[150_867, 150_868, 180, 150_863, 150_864, 150_858, 150_857],
			[150_867, 150_868, 200, 150_863, 150_864, 150_858, 150_857],
		];
		$unfinished = [
			0 => [
				'left'  => [150_846],
				'right' => [150_843],
			],
			1 => [
				'left'  => [150_846],
				'right' => [150_843],
			],
			2 => [
				'left'  => [150_836, 150_841],
				'right' => [150_833, 150_847],
			],
			3 => [
				'left'  => [150_834, 150_832, 150_842],
				'right' => [150_820, 150_818, 150_844],
			],
			4 => [
				'left'  => [150_821, 150_828, 150_825, 150_840],
				'right' => [150_831, 150_829, 150_826, 150_837],
			],
			5 => [
				'left'  => [150_835, 150_830, 150_827, 150_824, 150_838],
				'right' => [150_817, 150_819, 150_823, 150_822, 150_845],
			],
		];
		$finished = [
			0 => [
				'left'  => 150_855,
				'right' => 150_856,
			],
			1 => [
				'left'  => 150_855,
				'right' => 150_856,
			],
			2 => [
				'left'  => 150_839,
				'right' => 150_851,
			],
			3 => [
				'left'  => 150_854,
				'right' => 150_848,
			],
			4 => [
				'left'  => 150_852,
				'right' => 150_849,
			],
			5 => [
				'left'  => 150_853,
				'right' => 150_850,
			],
		];
		$icons = [
			151_026,
			151_023,
			151_024,
			151_025,
			151_022,
		];

		/** @var ArulSaba|null */
		$arul = $this->db->table(ArulSaba::getTable())
			->where('name', $type)
			->asObj(ArulSaba::class)
			->first();

		/** @psalm-suppress InvalidArrayOffset */
		if (!isset($arul) || ($numGems > 0 && !isset($blueprints[$numGems]))) {
			$context->reply("No Bracelet of Arul Saba ({$type} - {$numGems}/{$numGems}) found.");
			return;
		}
		$gems = [];
		$prefix = $numGems === 0 ? $arul->lesser_prefix : $arul->regular_prefix;
		$ingredients = new Ingredients();

		for ($i = 0; $i < $reqGems; $i++) {
			/** @psalm-suppress InvalidArrayOffset */
			$name = $gemGrades[$i][0] . " {$prefix} {$arul->name}";
			$ingredient = $this->readIngredientByName($name);
			if (!isset($ingredient->item)) {
				$context->reply("Your bot's item database is missing information to illustrate the process.");
				return;
			}
			$ingredients->add($ingredient);
			$gems []= $ingredient->item;
		}
		// A lot of the items used in the TS process are simply missing in the AODB
		// so we have to work around this, because no one wants them in searches anyway

		// Blueprints
		$bpQL = $blueprints[$numGems][2];
		$balId = $side === 'left' ? 3 : 5;
		$ingredient = $this->readIngredientByAoid($blueprints[$numGems][0], 1, $bpQL);
		if (!isset($ingredient->item)) {
			$context->reply("Item #{$blueprints[$numGems][0]} not found in bot's item database.");
			return;
		}
		$ingredients->add($ingredient);
		$bPrint = $ingredient->item;
		$bPrint->ql = $bpQL;
		$bbPrint = clone $bPrint;
		$bbPrint->lowid = $blueprints[$numGems][$balId];
		$bbPrint->highid = $blueprints[$numGems][$balId+1];
		$bbPrint->name = 'Balanced Bracelet Blueprints';

		// Adjuster
		$ingredient = $this->readIngredientByName('Balance Adjuster - ' . ucfirst($side));
		$ingredients->add($ingredient);
		$adjuster = $ingredient->item;
		// Ingots
		$minIngotQL = (int)ceil(0.7 * $bpQL);
		$ingredient = $this->readIngredientByName('Small Silver Ingot', $reqGems+1, $minIngotQL, true);
		$ingredients->add($ingredient);
		$ingot = $ingredient->item;
		// Furnace
		$ingredient = $this->readIngredientByName('Personal Furnace', $reqGems+1);
		$ingredients->add($ingredient);
		$furnace = $ingredient->item;
		// Robot Junk
		$minJunkQL = (int)ceil(0.53 * $bpQL);
		$ingredient = $this->readIngredientByName('Robot Junk', $reqGems, $minJunkQL, true);
		$ingredients->add($ingredient);
		$junk = $ingredient->item;
		// Wire
		$minWireQL = (int)ceil(0.35 * $bpQL);
		$ingredient = $this->readIngredientByName('Nano Circuitry Wire', $reqGems*2, $minWireQL, true);
		$ingredients->add($ingredient);
		$wire = $ingredient->item;
		// Wire Drawing Machine
		$ingredient = $this->readIngredientByName('Wire Drawing Machine', 1, 100, true);
		$ingredients->add($ingredient);
		$wireMachine = $ingredient->item;
		// Screwdriver
		$ingredient = $this->readIngredientByName('Screwdriver');
		$ingredients->add($ingredient);
		$screwdriver = $ingredient->item;

		if (!isset($adjuster)
			|| !isset($ingot)
			|| !isset($furnace)
			|| !isset($junk)
			|| !isset($wire)
			|| !isset($wireMachine)
			|| !isset($screwdriver)
		) {
			$context->reply('Your item database is missing some key items to illustrate the process.');
			return;
		}

		$blob = $this->renderIngredients($ingredients);

		$blob .= "<pagebreak><header2>Balancing the blueprint<end>\n".
			$this->renderStep($adjuster, $bPrint, $bbPrint, [self::ME => '*3', self::EE => '*3.2']);
		$liqSilver         = $this->itemsController->findByName('Liquid Silver', $ingot->ql);
		$silFilWire        = $this->itemsController->findByName('Silver Filigree Wire', $ingot->ql);
		$silNaCircWire     = $this->itemsController->findByName('Silver Nano Circuitry Filigree Wire', $ingot->ql);
		$nanoSensor        = $this->itemsController->findById(150_923);
		$intNanoSensor     = $this->itemsController->findById(150_926);
		$circuitry         = $this->itemsController->findByName('Bracelet Circuitry', $ingot->ql);
		if (!isset($liqSilver)
			|| !isset($silFilWire)
			|| !isset($silNaCircWire)
			|| !isset($nanoSensor)
			|| !isset($intNanoSensor)
			|| !isset($circuitry)
		) {
			$context->reply('Your item database is missing some key items to illustrate the process.');
			return;
		}
		$liqSilver->setQL($ingot->getQL());
		$silFilWire->setQL($liqSilver->getQL());
		$silNaCircWire->setQL($silFilWire->getQL());
		$nanoSensor = $nanoSensor->atQL(min(250, $junk->getQL()));
		$intNanoSensor = $intNanoSensor->atQL($nanoSensor->getQL());
		$circuitry->setQL($silNaCircWire->getQL());

		$blob .= "\n<pagebreak><header2>Bracelet circuitry ({$reqGems}x)<end>\n".
			$this->renderStep($furnace, $ingot, $liqSilver, [self::ME => '*3']).
			$this->renderStep($wireMachine, $liqSilver, $silFilWire, [self::ME => '*4.5']).
			$this->renderStep($wire, $silFilWire, $silNaCircWire, [self::ME => '*4',    self::AGI => '*1.7']).
			$this->renderStep($screwdriver, $junk, $nanoSensor).
			$this->renderStep($wire, $nanoSensor, $intNanoSensor, [self::ME => '*3.5',  self::EE  => '*4.25']).
			$this->renderStep($intNanoSensor, $silNaCircWire, $circuitry, [self::ME => '*4.25', self::EE  => '*4.8', self::AGI => '*1.8']);

		$socket = ($reqGems > 1) ? "{$reqGems} sockets" : 'a socket';
		$blob .= "\n<pagebreak><header2>Add {$socket} to the bracelet<end>\n";
		$target = $bbPrint;
		for ($i = 0; $i < $reqGems; $i++) {
			$result = clone $target;

			/** @psalm-suppress InvalidArrayOffset */
			$result->highid = $result->lowid = $unfinished[$numGems][$side][$i];

			/** @psalm-suppress InvalidArrayOffset */
			$result->icon = $icons[$i];
			$result->name = 'Unfinished Bracelet of Arul Saba';

			$result->ql = $result->lowql;
			$blob .= $this->renderStep($circuitry, $target, $result, [self::ME => '*4', self::EE => '*4.2']);
			$target = $result;
		}
		if (!isset($result)) {
			$context->reply('You managed to break the module. Great.');
			return;
		}

		/** @var AODBItem $result */
		$coated = clone $result;
		$coated->lowid = $coated->highid = $finished[$numGems][$side];
		$coated->name = 'Bracelet of Arul Saba';
		$blob .= "\n<pagebreak><header2>Add silver coating<end>\n".
			$this->renderStep($furnace, $ingot, $liqSilver, [self::ME => '*3']).
			$this->renderStep($liqSilver, $result, $coated);

		$blob .= "\n<pagebreak><header2>Add the gems<end>\n";
		$target = $coated;

		for ($i = 0; $i < $reqGems; $i++) {
			$gem = $gems[$i];
			$resultName = "Bracelet of Arul Saba ({$prefix} {$arul->name} - ".
				($i + 1) . "/{$reqGems} - ".
				ucfirst($side) . ')';
			$result = $this->itemsController->findByName($resultName);
			if (!isset($result)) {
				$context->reply("Unable to find the item {$resultName} in your bot's item database.");
				return;
			}
			$result = $result->atQL($result->getLowQL());

			/** @psalm-suppress InvalidArrayOffset */
			$blob .= $this->renderStep($gem, $target, $result, [self::ME => $gemGrades[$i][2], self::EE => $gemGrades[$i][3]]);
			$target = $result;
		}

		$blob .= "\n\n<i>The number in brackets behind a skill requirement is ".
			'how many times the QL of the target item is actually required '.
			'to do the tradeskill. The example numbers listed are only correct '.
			'for the exact QLs shown in the equation</i>';

		$msg = $this->text->makeBlob(
			"Recipe for a Bracelet of Arul Saba ({$prefix} {$arul->name} - ".
			"{$reqGems}/{$reqGems} - " . ucfirst($side) . ')',
			$blob
		);
		$context->reply($msg);
	}

	/** Render the given ingredients to a blob */
	public function renderIngredients(Ingredients $ingredients): string {
		$blob = "<header2>Ingredients<end>\n";
		$maxAmount = $ingredients->getMaxAmount();
		foreach ($ingredients as $ing) {
			$ql = (string)($ing->ql ?? '');
			if (isset($ing->item)) {
				$item = $ing->item;
				$link = $item->getLink(ql: $ing->ql ?? $item->lowql);
				if ($item->lowql === $item->highql) {
					$ql = '';
				}
			} else {
				$link = $ing->name;
			}
			if (strlen($ql)) {
				$ql = "QL{$ql}";
				if ($ing->qlCanBeHigher) {
					$ql .= '+';
				}
				$ql .= ' ';
			}
			if ($maxAmount === 1) {
				$amount = '';
			} elseif ($ing->amount === 1) {
				$amount = '<black>' . str_repeat('0', strlen((string)$maxAmount)-1) . '1×<end> ';
			} else {
				$amount = Text::alignNumber($ing->amount, strlen((string)$maxAmount), 'orange') . '× ';
			}
			$blob .= "<tab>{$amount}{$ql}{$link}";
			if (isset($ing->where)) {
				$blob .= " ({$ing->where})";
			}
			$blob .= "\n";
		}
		return "{$blob}\n";
	}

	protected function enrichIngredient(Ingredient $ing, int $amount, ?int $ql=null, bool $qlCanBeHigher=false): Ingredient {
		$ing->qlCanBeHigher = $qlCanBeHigher;
		if (isset($ql)) {
			$ing->ql = $ql;
		}
		$ing->amount = $amount;
		if (!isset($ing->aoid)) {
			return $ing;
		}
		$item = $this->itemsController->findById($ing->aoid);
		if (isset($item)) {
			$ing->item = $item->atQL($ql ?? $item->getLowQL());
		}
		return $ing;
	}

	/** @param array<int,string|int> $skillReqs */
	protected function renderStep(AODBItem $source, AODBItem $dest, AODBItem $result, array $skillReqs=[]): string {
		$showImages = $this->arulsabaShowImages;
		$sLink = $source->getLink();
		$sIcon = Text::makeImage($source->icon);
		$sIconLink = $source->getLink(text: $sIcon);
		$dLink = $dest->getLink();
		$dIcon = Text::makeImage($dest->icon);
		$dIconLink = $dest->getLink(text: $dIcon);
		$rLink = $result->getLink();
		$rIcon = Text::makeImage($result->icon);
		$rIconLink = $result->getLink(text: $rIcon);

		$line = '';

		if ($showImages === 1) {
			$sIconLink = $sIcon;
			$dIconLink = $dIcon;
			$rIconLink = $rIcon;
		}
		if ($showImages) {
			$line = '<tab>'.
				$sIconLink.
				'<tab><img src=tdb://id:GFX_GUI_CONTROLCENTER_BIGARROW_RIGHT_STATE1><tab>'.
				$dIconLink.
				'<tab><img src=tdb://id:GFX_GUI_CONTROLCENTER_BIGARROW_RIGHT_STATE1><tab>'.
				$rIconLink . "\n";
		}
		$line .= "<tab>{$sLink} + {$dLink} = {$rLink}";
		if (
			(ItemFlag::NO_DROP->notIn($dest->flags??0))
			&& (ItemFlag::NO_DROP->in($result->flags??0))
		) {
			$line .= ' (becomes <highlight>NODROP<end>)';
		}
		$line .= "\n";
		if (!count($skillReqs)) {
			$line .= "<tab><yellow>No skills required<end>\n\n";
			if ($showImages) {
				$line .= "\n";
			}
			return $line;
		}
		$requirements = [];
		foreach ($skillReqs as $skillID => $amount) {
			$amount = (string)$amount;
			$skill = $this->readSkill($skillID);
			if (!isset($skill)) {
				throw new Exception("Unable to find skill {$skillID}");
			}
			if (substr($amount, 0, 1) === '*') {
				$exAmount = (int)ceil((float)substr($amount, 1) * $dest->ql);
				$requirements []= "<yellow>{$skill->name}: {$exAmount}<end> (" . substr($amount, 1) . 'x)';
			} else {
				$exAmount = (int)$amount;
				$requirements []= "<yellow>{$skill->name}: {$exAmount}<end>";
			}
		}
		$line .= '<tab>' . implode(', ', $requirements) . "\n\n";
		if ($showImages) {
			$line .= "\n";
		}
		return $line;
	}

	protected function readSkill(int $id): ?Skill {
		return $this->db->table(Skill::getTable())
			->where('id', $id)
			->asObj(Skill::class)
			->first();
	}
}
