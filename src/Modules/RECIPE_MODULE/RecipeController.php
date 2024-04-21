<?php declare(strict_types=1);

namespace Nadybot\Modules\RECIPE_MODULE;

use function Safe\json_decode;
use Amp\File\{FilesystemException};
use Exception;
use Nadybot\Core\Events\ConnectEvent;
use Nadybot\Core\{
	Attributes as NCA,
	CmdContext,
	DB,
	Exceptions\UserException,
	Filesystem,
	ModuleInstance,
	ParamClass\PItem,
	Safe,
	Text,
};
use Nadybot\Modules\ITEMS_MODULE\{
	AODBItem,
	ItemsController,
};
use Safe\Exceptions\JsonException;

/**
 * @author Tyrence
 * Based on a module written by Captainzero (RK1) of the same name for an earlier version of Budabot
 */
#[
	NCA\Instance,
	NCA\HasMigrations('Migrations/Recipes'),
	NCA\DefineCommand(
		command: 'recipe',
		accessLevel: 'guest',
		description: 'Search for a recipe',
	)
]
class RecipeController extends ModuleInstance {
	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private Filesystem $fs;

	#[NCA\Inject]
	private ItemsController $itemsController;

	private string $path;

	/** This is an Event("connect") instead of Setup since it depends on the items db being loaded */
	#[NCA\Event(
		name: ConnectEvent::EVENT_MASK,
		description: 'Initializes the recipe database',
		defaultStatus: 1
	)]
	public function connectEvent(): void {
		$this->path = __DIR__ . '/recipes/';
		try {
			$fileNames = $this->fs->listFiles($this->path);
		} catch (FilesystemException $e) {
			throw new Exception("Could not open '{$this->path}' for loading recipes: " . $e->getMessage());
		}

		/** @var array<string,Recipe> */
		$recipes = $this->db->table(Recipe::getTable())->asObj(Recipe::class)->keyBy('id')->toArray();
		foreach ($fileNames as $fileName) {
			if (!count($args = Safe::pregMatch("/(\d+)\.(txt|json)$/", $fileName))) {
				continue;
			}
			if (isset($recipes[$args[1]])) {
				if (($this->fs->getModificationTime($this->path . $fileName)) === $recipes[$args[1]]->date) {
					continue;
				}
			}
			// if file has the correct extension, load recipe into database
			if ($args[2] === 'txt') {
				$recipe = $this->parseTextFile((int)$args[1], $fileName);
			} elseif ($args[2] === 'json') {
				$recipe = $this->parseJSONFile((int)$args[1], $fileName);
			} else {
				continue;
			}
			if (isset($recipes[$args[1]])) {
				$this->db->update($recipe);
			} else {
				$this->db->insert($recipe);
			}
		}
	}

	/** Show a specific recipe */
	#[NCA\HandlesCommand('recipe')]
	public function recipeShowCommand(CmdContext $context, int $id): void {
		/** @var ?Recipe */
		$row = $this->db->table(Recipe::getTable())->where('id', $id)->asObj(Recipe::class)->first();

		if ($row === null) {
			$msg = "Could not find recipe with id <highlight>{$id}<end>.";
		} else {
			$msg = $this->createRecipeBlob($row);
		}
		$context->reply($msg);
	}

	/** Search for a recipe */
	#[NCA\HandlesCommand('recipe')]
	public function recipeSearchCommand(CmdContext $context, string $search): void {
		$query = $this->db->table(Recipe::getTable())
			->orderBy('name');
		if (PItem::matches($search)) {
			$item = new PItem($search);
			$search = $item->name;

			$query->whereIlike('recipe', "%{$item->lowID}%")
				->orWhereIlike('recipe', "%{$item->name}%");
		} else {
			$this->db->addWhereFromParams($query, explode(' ', $search), 'recipe');
		}

		/** @var Recipe[] */
		$data = $query->asObj(Recipe::class)->toArray();

		$count = count($data);

		if ($count === 0) {
			$msg = 'Could not find any recipes matching your search criteria.';
			$context->reply($msg);
			return;
		}
		if ($count === 1) {
			$msg = $this->createRecipeBlob($data[0]);
			$context->reply($msg);
			return;
		}
		$blob = "<header2>Recipes containing \"{$search}\"<end>\n";
		foreach ($data as $row) {
			$blob .= '<tab>' . Text::makeChatcmd($row->name, "/tell <myname> recipe {$row->id}") . "\n";
		}

		$msg = $this->text->makeBlob("Recipes matching '{$search}' ({$count})", $blob);

		$context->reply($msg);
	}

	public function formatRecipeText(string $input): string {
		$input = str_replace('\\n', "\n", $input);
		$input = preg_replace_callback('/#L "([^"]+)" "([0-9]+)"/', $this->replaceItem(...), $input);
		$input = Safe::pregReplace('/#L "([^"]+)" "([^"]+)"/', "<a href='chatcmd://\\2'>\\1</a>", $input);

		// we can't use <myname> in the sql since that will get converted on load,
		// and we need to wait to convert until display time due to the possibility
		// of several bots sharing the same db
		$input = str_replace('{myname}', '<myname>', $input);

		return $input;
	}

	/** @return list<string> */
	public function createRecipeBlob(Recipe $row): array {
		$recipe_name = $row->name;
		$author = empty($row->author) ? 'Unknown' : $row->author;

		$recipeText = "Recipe Id: <highlight>{$row->id}<end>\n";
		$recipeText .= "Author: <highlight>{$author}<end>\n\n";
		$recipeText .= $this->formatRecipeText($row->recipe);

		return (array)$this->text->makeBlob("Recipe for {$recipe_name}", $recipeText);
	}

	private function parseTextFile(int $id, string $fileName): Recipe {
		$lines = explode("\n", $this->fs->read($this->path . $fileName));
		$nameLine = trim(array_shift($lines));
		$authorLine = trim(array_shift($lines));
		$recipe = new Recipe(
			id: $id,
			name: (strlen($nameLine) > 6) ? substr($nameLine, 6) : 'Unknown',
			author: (strlen($authorLine) > 8) ? substr($authorLine, 8) : 'Unknown',
			recipe: implode("\n", $lines),
			date: $this->fs->getModificationTime($this->path . $fileName),
		);

		return $recipe;
	}

	private function parseJSONFile(int $id, string $fileName): Recipe {
		try {
			$data = json_decode($this->fs->read($this->path . $fileName), false);
		} catch (JsonException $e) {
			throw new UserException("Could not read '{$fileName}': invalid JSON");
		}

		/** @var array<string,AODBItem> */
		$items = [];
		foreach ($data->items as $item) {
			$dbItem = $this->itemsController->findById($item->item_id)?->atQL($item->ql);
			if ($dbItem === null) {
				throw new UserException("Could not find item '{$item->item_id}'");
			}
			$items[$item->alias] = $dbItem;
			$items[$item->alias]->ql = $item->ql;
		}

		$recipe = "<font color=#FFFF00>------------------------------</font>\n";
		$recipe .= "<font color=#FF0000>Ingredients</font>\n";
		$recipe .= "<font color=#FFFF00>------------------------------</font>\n\n";
		$ingredients = $items;
		foreach ($data->steps as $step) {
			unset($ingredients[$step->result]);
		}
		foreach ($ingredients as $ingredient) {
			$recipe .= $ingredient->getIcon() . "\n";
			$recipe .= $ingredient->getLink() . "\n\n\n";
		}

		$recipe .= "<pagebreak><yellow>------------------------------<end>\n";
		$recipe .= "<red>Recipe<end>\n";
		$recipe .= "<yellow>------------------------------<end>\n\n";
		$stepNum = 1;
		foreach ($data->steps as $step) {
			$recipe .= "<pagebreak><header2>Step {$stepNum}<end>\n";
			$stepNum++;
			$source = $items[$step->source];
			$target = $items[$step->target];
			$result = $items[$step->result];
			$recipe .= '<tab>'.
				$source->getLink(text: $source->getIcon()).
				'<tab><img src=tdb://id:GFX_GUI_CONTROLCENTER_BIGARROW_RIGHT_STATE1><tab>'.
				$target->getLink(text: $target->getIcon()).
				'<tab><img src=tdb://id:GFX_GUI_CONTROLCENTER_BIGARROW_RIGHT_STATE1><tab>'.
				$result->getLink(text: $result->getIcon()).
				"\n";
			$recipe .= "<tab>{$source->name} ".
				"<highlight>+<end> {$target->name} <highlight>=<end> ".
				$result->getLink() . "\n";
			if ($step->skills) {
				$recipe .= "<tab><yellow>Skills: {$step->skills}<end>\n";
			}
			$recipe .= "\n\n";
		}
		return new Recipe(
			id: $id,
			name: $data->name ?? '<unnamed>',
			author: $data->author ?? '<unknown>',
			date: $this->fs->getModificationTime($this->path . $fileName),
			recipe: $recipe,
		);
	}

	/** @param string[] $arr */
	private function replaceItem(array $arr): string {
		$id = (int)$arr[2];
		$row = $this->itemsController->findById($id);
		if ($row !== null) {
			$output = $row->getLink(ql: $row->highql);
		} else {
			$output = "#L \"{$arr[1]}\" \"/tell <myname> itemid {$arr[2]}\"";
		}
		return $output;
	}
}
