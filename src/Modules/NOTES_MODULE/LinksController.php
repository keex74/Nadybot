<?php declare(strict_types=1);

namespace Nadybot\Modules\NOTES_MODULE;

use Nadybot\Core\{
	AccessManager,
	Attributes as NCA,
	CmdContext,
	DB,
	ModuleInstance,
	ParamClass\PRemove,
	ParamClass\PWord,
	Text,
};

/**
 * @author Tyrence (RK2)
 */
#[
	NCA\Instance,
	NCA\HasMigrations('Migrations/Links'),
	NCA\DefineCommand(
		command: 'links',
		accessLevel: 'guild',
		description: 'Displays, adds, or removes links from the org link list',
	),
]
class LinksController extends ModuleInstance {
	/** Enable full urls in the link list output */
	#[NCA\Setting\Boolean]
	public bool $showfullurls = false;

	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private AccessManager $accessManager;

	/** Show all links */
	#[NCA\HandlesCommand('links')]
	public function linksListCommand(CmdContext $context): void {
		$links = $this->db->table(Link::getTable())
			->orderBy('name')
			->asObj(Link::class);
		if ($links->count() === 0) {
			$msg = 'No links found.';
			$context->reply($msg);
			return;
		}

		$blob = "<header2>All my links<end>\n";
		foreach ($links as $link) {
			$remove = Text::makeChatcmd('remove', "/tell <myname> links rem {$link->id}");
			if ($this->showfullurls) {
				$website = Text::makeChatcmd($link->website, "/start {$link->website}");
			} else {
				$website = '[' . Text::makeChatcmd('visit', "/start {$link->website}") . ']';
			}
			$blob .= "<tab>{$website} <highlight>{$link->comments}<end> (by {$link->name}) [{$remove}]\n";
		}

		$msg = $this->text->makeBlob('Links', $blob);
		$context->reply($msg);
	}

	/** Add a link to the list */
	#[NCA\HandlesCommand('links')]
	public function linksAddCommand(CmdContext $context, #[NCA\Str('add')] string $action, PWord $url, string $comments): void {
		$website = htmlspecialchars($url());
		if (filter_var($website, \FILTER_VALIDATE_URL) === false) {
			$msg = "<highlight>{$website}<end> is not a valid URL.";
			$context->reply($msg);
			return;
		}

		$this->db->insert(new Link(
			name: $context->char->name,
			website: $website,
			comments: $comments,
			dt: time(),
		));
		$msg = 'Link added successfully.';
		$context->reply($msg);
	}

	/** Remoev a link from the list */
	#[NCA\HandlesCommand('links')]
	public function linksRemoveCommand(CmdContext $context, PRemove $action, int $id): void {
		/** @var ?Link */
		$obj = $this->db->table(Link::getTable())
			->where('id', $id)
			->asObj(Link::class)
			->first();
		if ($obj === null) {
			$msg = "Link with ID <highlight>{$id}<end> could not be found.";
		} elseif ($obj->name == $context->char->name
			|| $this->accessManager->compareCharacterAccessLevels($context->char->name, $obj->name) > 0) {
			$this->db->table(Link::getTable())->delete($id);
			$msg = "Link with ID <highlight>{$id}<end> deleted successfully.";
		} else {
			$msg = "You do not have permission to delete links added by <highlight>{$obj->name}<end>";
		}
		$context->reply($msg);
	}
}
