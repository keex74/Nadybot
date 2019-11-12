<?php

namespace Budabot\User\Modules;

use Budabot\Core\StopExecutionException;
use Exception;

/**
 * Authors:
 *	- Tyrence (RK2)
 *
 * @Instance
 *
 * Commands this controller contains:
 *	@DefineCommand(
 *		command     = 'shop',
 *		accessLevel = 'all',
 *		description = 'Search for things that have been posted to the shopping channels',
 *		help        = 'shop.txt'
 *	)
 */
class ShoppingController {

	/**
	 * Name of the module.
	 * Set automatically by module loader.
	 */
	public $moduleName;
	
	/**
	 * @var \Budabot\Core\Budabot $chatBot
	 * @Inject
	 */
	public $chatBot;

	/**
	 * @var \Budabot\Core\Text $text
	 * @Inject
	 */
	public $text;
	
	/**
	 * @var \Budabot\Core\Util $util
	 * @Inject
	 */
	public $util;
	
	/**
	 * @var \Budabot\Core\Http $http
	 * @Inject
	 */
	public $http;
	
	/**
	 * @var \Budabot\Core\DB $db
	 * @Inject
	 */
	public $db;
	
	/**
	 * @var \Budabot\Core\SettingManager $settingManager
	 * @Inject
	 */
	public $settingManager;
	
	/**
	 * @var \Budabot\Core\BanController $banController
	 * @Inject
	 */
	public $banController;
	
	/**
	 * @var \Budabot\Core\PlayerManager $playerManager
	 * @Inject
	 */
	public $playerManager;
	
	/**
	 * @var \Budabot\Core\ItemsController $itemsController
	 * @Inject
	 */
	public $itemsController;
	
	/** @Logger */
	public $logger;
	
	/** @Setup */
	public function setup() {
		$this->db->loadSQLFile($this->moduleName, "shopping_messages");
		$this->db->loadSQLFile($this->moduleName, "shopping_items");
		
		$this->settingManager->add(
			$this->moduleName,
			"shop_message_age",
			"How long to keep shopping messages",
			"edit",
			"time",
			"10d",
			"1d;2d;5d;10d;15d;20d"
		);
		$this->settingManager->add(
			$this->moduleName,
			"shop_database",
			"Where to look for shopping messages",
			"edit",
			"text",
			"http://shopping.budabot.jkbff.com/shopping/index.php",
			"local;http://shopping.budabot.jkbff.com/shopping/index.php"
		);
	}

	/**
	 * @HandlesCommand("shop")
	 * @Matches("/^shop (\d+) (\d+) (.+)$/i")
	 * @Matches("/^shop (\d+) (.+)$/i")
	 * @Matches("/^shop (.+)$/i")
	 */
	public function shopCommand($message, $channel, $sender, $sendto, $args) {
		if (count($args) == 4) {
			$minQl = $args[1];
			$maxQl = $args[2];
			$search = $args[3];
		} elseif (count($args) == 3) {
			$minQl = $args[1];
			$maxQl = $args[1];
			$search = $args[2];
		} else {
			$minQl = 0;
			$maxQl = 500;
			$search = $args[1];
		}

		$shopDatabase = $this->settingManager->get('shop_database');
		if ($shopDatabase == 'local') {
			$results = $this->searchLocal($search, $minQl, $maxQl);
		} else {
			$results = $this->searchRemote($shopDatabase, $search, $minQl, $maxQl);
		}
		
		$count = count($results);
		if ($count == 0) {
			$msg = "No results were found matching your search criteria.";
		} else {
			$blob = '';
			forEach ($results as $result) {
				$senderLink = $this->text->makeUserlink($result->sender);
				$timeString = $this->util->unixtimeToReadable(time() - $result->time, false);
				$post = preg_replace('|<a href="itemref://(\d+)/(\d+)/(\d+)">([^<]+)</a>|', "<a href='itemref://\\1/\\2/\\3'>\\4</a>", $result->message);
				$blob .= "[$senderLink]: {$post} - <highlight>($timeString ago)<end>\n\n";
			}
			$msg = $this->text->makeBlob("Shopping Results for '$search' ($count)", $blob);
		}
		
		$sendto->reply($msg);
	}

	public function searchRemote($url, $search, $minQl, $maxQl) {
		$params = array(
			'server' => $this->chatBot->vars['dimension'],
			'search' => $search,
			'minql' => $minQl,
			'maxql' => $maxQl,
			'bot' => 'budabot'
		);
		$response = $this->http->get($url)->withQueryParams($params)->waitAndReturnResponse();
		if (!empty($response->error)) {
			throw new Exception($response->error);
		} elseif (substr($response->body, 0, 5) == 'Error') {
			throw new Exception($response->body);
		} else {
			return json_decode($response->body);
		}
	}

	public function searchLocal($search, $minQl, $maxQl) {
		list($query, $params) = $this->util->generateQueryFromParams(explode(' ', $search), 's1.name');
		
		$params []= $minQl;
		$params []= $maxQl;
		
		$sql = "
			SELECT
				sender,
				message,
				MAX(dt) as time
			FROM
				shopping_items s1
				JOIN shopping_messages s2
					ON s1.message_id = s2.id
			WHERE
				s2.dimension = <dim>
				AND $query
				AND s1.ql >= ?
				AND s1.ql <= ?
			GROUP BY
				sender,
				message
			ORDER BY
				MAX(dt) DESC
			LIMIT
				40";

		return $this->db->query($sql, $params);
	}
	
	/**
	 * @Event("packet(65)")
	 * @Description("Capture messages from shopping channel")
	 */
	public function captureShoppingMessagesEvent($eventObj) {
		$packet = $eventObj->packet;

		$b = unpack("C*", $packet->args[0]);
		// check to make sure message is from a shopping channel
		// (first byte = 134; see http://aodevs.com/forums/index.php/topic,42.msg2192.html#msg2192)
		if ($b[1] != 134) {
			return;
		}

		$charId = $packet->args[1];
		$channel = $this->chatBot->get_gname($packet->args[0]);
		$sender	= $this->chatBot->lookup_user($charId);
		$message = $packet->args[2];
		
		if ($this->banController->isBanned($charId)) {
			return;
		}
		
		$this->logger->logChat($channel, $sender, $message);

		$this->processShoppingMessage($channel, $sender, $message);
	}
	
	/**
	 * @Event("timer(24hrs)")
	 * @Description("Remove old shopping messages from the database")
	 */
	public function removeOldMessagesEvent($eventObj) {
		$dt = time() - $this->settingManager->get('shop_message_age');

		$sql = "DELETE FROM shopping_messages WHERE dt < ?";
		$this->db->exec($sql, $dt);

		$sql = "DELETE FROM shopping_items WHERE message_id NOT IN (SELECT id FROM shopping_messages)";
		$this->db->exec($sql);
	}
	
	public function processShoppingMessage($channel, $sender, $message) {
		$message = preg_replace("|<font(.+)>|U", "", $message);
		$message = preg_replace("|</font>|U", "", $message);
		
		// messageType: 1=WTS, 2=WTB, 3=WTT, 4=WTH, default to WTS
		$messageType = 1;
		if (preg_match("/^(.{0,3})wtb/i", $message)) {
			$messageType = 2;
		} elseif (preg_match("/^(.{0,3})wtt/i", $message)) {
			$messageType = 3;
		} elseif (preg_match("/^(.{0,3})wth/i", $message)) {
			$messageType = 4;
		}
		
		$matches = array();
		$pattern = '|<a href="itemref://(\d+)/(\d+)/(\d+)">([^<]+)</a>|';
		preg_match_all($pattern, $message, $matches, PREG_SET_ORDER);

		$sql = "INSERT INTO shopping_messages (dimension, message_type, channel, bot, sender, dt, message) VALUES ('<dim>', ?, ?, '<myname>', ?, ?, ?)";
		$this->db->exec($sql, $messageType, $channel, $sender, time(), $message);
		$id = $this->db->lastInsertId();
		
		forEach ($matches as $match) {
			$lowid = $match[1];
			$highid = $match[2];
			$ql = $match[3];
			$name = $match[4];

			$item = $this->itemsController->findById($lowid);
			$iconid = 0;
			if ($item !== null) {
				$iconid = $item->icon;
			}

			$sql = "INSERT INTO shopping_items (message_id, lowid, highid, ql, iconid, name) VALUES (?, ?, ?, ?, ?, ?)";
			$this->db->exec($sql, $id, $lowid, $highid, $ql, $iconid, $name);
		}
		
		$this->playerManager->getByName($sender);
	}

	public function parseSpamMessage($message) {
		$rawmsg = $this->stripColors($message);
		if (preg_match_all("/\\[([^\\]]+)\\] (.+?) \\[([^\\]]+)\\]/s", $rawmsg, $arr, PREG_SET_ORDER) > 0) {
		} else {
			$this->logger->log("WARN", "Invalid spam message format: $rawmsg");
		}
		return $arr;
	}
	
	private function parseSpambotMessage($eventObj) {
		$arr = $this->parseSpamMessage($eventObj->message);
		forEach ($arr as $entry) {
			$channel = $entry[1];
			$text = $entry[2];
			$sender = $entry[3];
			
			$this->processShoppingMessage("$eventObj->sender - $channel", $sender, $text);
		}
	}
}
