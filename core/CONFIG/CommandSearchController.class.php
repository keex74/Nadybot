<?php

namespace Budabot\Core\Modules;

/**
 * @Instance
 *
 * Commands this controller contains:
 *	@DefineCommand(
 *		command       = 'cmdsearch',
 *      alias         = 'searchcmd',
 *		accessLevel   = 'all',
 *		description   = 'Finds commands based on key words',
 *		defaultStatus = 1,
 *		help          = 'cmdsearch.txt'
 *	)
 */
class CommandSearchController {

	/**
	 * @var \Budabot\Core\Budabot $chatBot
	 * @Inject
	 */
	public $chatBot;

	/**
	 * @var \Budabot\Core\DB $db
	 * @Inject
	 */
	public $db;

	/**
	 * @var \Budabot\Core\Text $text
	 * @Inject
	 */
	public $text;

	/**
	 * @var \Budabot\Core\AccessManager $accessManager
	 * @Inject
	 */
	public $accessManager;

	private $searchWords;

	/**
	 * @HandlesCommand("cmdsearch")
	 * @Matches("/^cmdsearch (.*)/i")
	 */
	public function searchCommand($message, $channel, $sender, $sendto, $arr) {
		$this->searchWords = explode(" ", $arr[1]);

		// if a mod or higher, show all commands, not just enabled commands
		$access = false;
		if ($this->accessManager->checkAccess($sender, 'mod')) {
			$access = true;
		}

		$sqlquery = "SELECT DISTINCT module, cmd, help, description, admin FROM cmdcfg_<myname> WHERE cmd = ?";
		if (!$access) {
			$sqlquery .= " AND status = 1";
		}
		$results = $this->db->query($sqlquery, $arr[1]);
		$results = $this->filterResultsByAccessLevel($sender, $results);

		$exactMatch = !empty($results);

		if (!$exactMatch) {
			$results = $this->findSimilarCommands($this->searchWords, $access);
			$results = $this->filterResultsByAccessLevel($sender, $results);
			$results = array_slice($results, 0, 5);
		}

		$msg = $this->render($results, $access, $exactMatch);

		$sendto->reply($msg);

		return true;
	}
	
	public function filterResultsByAccessLevel($sender, $data) {
		$results = array();
		$charAccessLevel = $this->accessManager->getSingleAccessLevel($sender);
		forEach ($data as $key => $row) {
			if ($this->accessManager->compareAccessLevels($charAccessLevel, $row->admin) >= 0) {
				$results []= $row;
			}
		}
		return $results;
	}
	
	public function findSimilarCommands($wordArray, $includeDisabled=false) {
		$sqlquery = "SELECT DISTINCT module, cmd, help, description, admin FROM cmdcfg_<myname>";
		if (!$includeDisabled) {
			$sqlquery .= " WHERE status = 1";
		}
		$data = $this->db->query($sqlquery);

		forEach ($data as $row) {
			$keywords = array($row->cmd);
			$keywords = array_unique($keywords);
			$row->distance = 0;
			forEach ($wordArray as $searchWord) {
				$distance = 9999;
				forEach ($keywords as $keyword) {
					$distance = min($distance, levenshtein($keyword, $searchWord));
				}
				$row->distance += $distance;
			}
		}
		$results = $data;
		usort($results, array($this, 'sortByDistance'));
		
		return $results;
	}

	public function sortByDistance($row1, $row2) {
		$d1 = $row1->distance;
		$d2 = $row2->distance;
		if ($d1 == $d2) {
			return 0;
		}
		return ($d1 < $d2) ? -1 : 1;
	}

	public function render($results, $hasAccess, $exactMatch) {
		$blob = '';
		forEach ($results as $row) {
			if ($row->help != '') {
				$helpLink = ' (' . $this->text->makeChatcmd("Help", "/tell <myname> help $row->cmd") . ')';
			}
			if ($hasAccess) {
				$module = $this->text->makeChatcmd($row->module, "/tell <myname> config {$row->module}");
			} else {
				$module = "{$row->module}";
			}

			$cmd = str_pad($row->cmd . " ", 20, ".");
			$blob .= "<highlight>{$cmd}<end> {$module} - {$row->description}{$helpLink}\n";
		}

		$count = count($results);
		if ($count == 0) {
			$msg = "No results found.";
		} else {
			if ($exactMatch) {
				$msg = $this->text->makeBlob("Command Search Results ($count)", $blob);
			} else {
				$msg = $this->text->makeBlob("Possible Matches ($count)", $blob);
			}
		}
		return $msg;
	}
}
