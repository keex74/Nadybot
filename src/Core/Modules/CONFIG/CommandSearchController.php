<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\CONFIG;

use Exception;
use Illuminate\Support\Collection;
use Nadybot\Core\DBSchema\CmdCfg;
use Nadybot\Core\{
	AccessManager,
	Attributes as NCA,
	CmdContext,
	DB,
	DBSchema\CmdPermission,
	DBSchema\CommandSearchResult,
	Exceptions\SQLException,
	ModuleInstance,
	Text,
};

#[
	NCA\Instance,
	NCA\DefineCommand(
		command: 'cmdsearch',
		accessLevel: 'guest',
		description: 'Finds commands based on key words',
		defaultStatus: 1,
		alias: 'searchcmd'
	)
]
class CommandSearchController extends ModuleInstance {
	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Text $text;

	#[NCA\Inject]
	private AccessManager $accessManager;

	/** Search for a command */
	#[NCA\HandlesCommand('cmdsearch')]
	public function searchCommand(CmdContext $context, string $search): void {
		$commands = $this->getAllCmds();
		$access = true;
		// if a mod or higher, show all commands, not just enabled commands
		if (!$this->accessManager->checkAccess($context->char->name, 'mod')) {
			$commands = $this->filterDisabled($commands);
			$access = false;
		}

		$commands = $this->filterResultsByAccessLevel($context->char->name, $commands);

		$exactMatch = $commands->where('cmd', $search)->first();

		if ($exactMatch) {
			$results = collect([$exactMatch]);
			$exactMatch = true;
		} else {
			$exactMatch = true;
			$results = $this->orderBySimilarity($commands, $search)->slice(0, 5);
		}

		$msg = $this->render($results, $access, $exactMatch);

		$context->reply($msg);
	}

	/**
	 * Remove all commands that we don't have access to
	 *
	 * @param Collection<int,CommandSearchResult> $data
	 *
	 * @return Collection<int,CommandSearchResult>
	 *
	 * @throws SQLException
	 * @throws Exception
	 */
	public function filterResultsByAccessLevel(string $sender, Collection $data): Collection {
		$charAccessLevel = $this->accessManager->getSingleAccessLevel($sender);
		return $data->filter(function (CommandSearchResult $cmd) use ($charAccessLevel): bool {
			$cmd->permissions = array_filter($cmd->permissions, function (CmdPermission $perm) use ($charAccessLevel): bool {
				return $this->accessManager->compareAccessLevels($charAccessLevel, $perm->access_level) >= 0;
			});
			return count($cmd->permissions) > 0;
		});
	}

	/**
	 * @param Collection<int,CommandSearchResult> $data
	 *
	 * @return Collection<int,CommandSearchResult>
	 */
	public function orderBySimilarity(Collection $data, string $search): Collection {
		return $data->each(static function (CommandSearchResult $row) use ($search): void {
			similar_text($row->cmd, $search, $row->similarity_percent);
		})->filter(static function (CommandSearchResult $row): bool {
			return $row->similarity_percent >= 66;
		})->sort(static function (CommandSearchResult $row1, CommandSearchResult $row2): int {
			return $row2->similarity_percent <=> $row1->similarity_percent;
		});
	}

	/**
	 * @param Collection<int,CommandSearchResult> $results
	 *
	 * @return string|list<string>
	 */
	public function render(Collection $results, bool $hasAccess, bool $exactMatch): string|array {
		$blob = '';
		foreach ($results as $row) {
			$helpLink = ' [' . Text::makeChatcmd('help', "/tell <myname> help {$row->cmd}") . ']';
			if ($hasAccess) {
				$module = Text::makeChatcmd($row->module, "/tell <myname> config {$row->module}");
			} else {
				$module = "{$row->module}";
			}

			$blob .= "<header2>{$row->cmd}<end>\n<tab>{$module} - {$row->description}{$helpLink}\n";
		}

		$count = $results->count();
		if ($results->isEmpty()) {
			return 'No results found.';
		}
		if ($exactMatch) {
			$msg = $this->text->makeBlob("Command Search Results ({$count})", $blob);
		} else {
			$msg = $this->text->makeBlob("Possible Matches ({$count})", $blob);
		}
		return $msg;
	}

	/** @return Collection<int,CommandSearchResult> */
	public function findSimilarCommands(string $search, string $sender): Collection {
		$commands = $this->getAllCmds();
		$commands = $this->filterDisabled($commands);
		$commands = $this->filterResultsByAccessLevel($sender, $commands);
		return $this->orderBySimilarity($commands, $search);
	}

	/** @return Collection<int,CommandSearchResult> */
	protected function getAllCmds(): Collection {
		$permissions = $this->db->table(CmdPermission::getTable())
			->asObj(CmdPermission::class)
			->groupBy('cmd');
		return $this->db->table(CmdCfg::getTable())
			->where('cmdevent', 'cmd')
			->asObj(CommandSearchResult::class)
			->each(static function (CommandSearchResult $cmd) use ($permissions): void {
				$cmd->permissions = $permissions->get($cmd->cmd, new Collection())
					->keyBy('permission_set')->toArray();
			});
	}

	/**
	 * @param Collection<int,CommandSearchResult> $commands
	 *
	 * @return Collection<int,CommandSearchResult>
	 */
	protected function filterDisabled(Collection $commands): Collection {
		return $commands->filter(static function (CommandSearchResult $cmd): bool {
			$cmd->permissions = array_filter($cmd->permissions, static function (CmdPermission $perm): bool {
				return $perm->enabled;
			});
			return count($cmd->permissions) > 0;
		})->values();
	}
}
