<?php declare(strict_types=1);

namespace Nadybot\Modules\PVP_MODULE;

use Illuminate\Support\Collection;
use Nadybot\Core\ParamClass\{PNonGreedy, PTowerSite};
use Nadybot\Core\Routing\{RoutableMessage, Source};
use Nadybot\Core\{Attributes as NCA, CmdContext, ConfigFile, DB, LoggerWrapper, MessageHub, ModuleInstance, QueryBuilder, Text, Util};
use Nadybot\Modules\HELPBOT_MODULE\{Playfield, PlayfieldController};
use Nadybot\Modules\LEVEL_MODULE\LevelController;
use Nadybot\Modules\PVP_MODULE\Event\TowerAttackInfo;

#[
	NCA\Instance,
	NCA\EmitsMessages("pvp", "tower-attack"),
	NCA\EmitsMessages("pvp", "tower-attack-own"),
	NCA\EmitsMessages("pvp", "tower-outcome"),
	NCA\EmitsMessages("pvp", "tower-outcome-own"),
	NCA\DefineCommand(
		command: AttacksController::CMD_ATTACKS,
		description: "Show the last Tower Attack messages",
		accessLevel: "guest",
	),
	NCA\DefineCommand(
		command: AttacksController::CMD_OUTCOMES,
		description: "Show the last tower outcomes",
		accessLevel: "guest",
	),
]
class AttacksController extends ModuleInstance {
	public const CMD_ATTACKS = "nw attacks";
	public const CMD_OUTCOMES = "nw victory";

	private const ATT_FMT_NORMAL = "{?att-org:{c-att-org}}{!att-org:{c-att-name}} attacked {c-def-org} ".
		"{?att-org:- {c-att-name} }{?att-level:({c-att-level}/{c-att-ai-level},{?att-gender: {att-gender} {att-breed}} {c-att-profession}{?att-org-rank:, {att-org-rank}})}";
	private const VICTORY_FMT_NORMAL = "{c-winning-org} won against {c-losing-org} in <highlight>{pf-short} {site-id}<end>";
	private const ABANDONED_FMT_NORMAL = "{c-losing-org} abandoned <highlight>{pf-short} {site-id}<end>";

	#[NCA\Inject]
	public SiteTrackerController $siteTracker;

	#[NCA\Inject]
	public PlayfieldController $pfCtrl;

	#[NCA\Inject]
	public NotumWarsController $nwCtrl;

	#[NCA\Inject]
	public MessageHub $msgHub;

	#[NCA\Inject]
	public LevelController $lvlCtrl;

	#[NCA\Inject]
	public ConfigFile $config;

	#[NCA\Inject]
	public DB $db;

	#[NCA\Inject]
	public Text $text;

	#[NCA\Inject]
	public Util $util;

	#[NCA\Logger]
	public LoggerWrapper $logger;

	/** Display format for tower attacks */
	#[NCA\Setting\Template(
		options: [
			self::ATT_FMT_NORMAL,
			"{?att-org:{c-att-org}}{!att-org:{c-att-name}} attacked {c-def-org}",
			"{?att-org:{c-att-org}}{!att-org:{c-att-name}} attacked {c-def-org} CT <highlight>{site-ct-ql}<end>",
		],
		exampleValues: [
			// ...TowerAttack::EXAMPLE_TOKENS,
			"att-org-name" => "Team Rainbow",
			"c-att-org-name" => "<clan>Team Rainbow<end>",
			"att-org" => "Team Rainbow",
			"c-att-org" => "<clan>Team Rainbow<end>",
			"att-org-faction" => 'Clan',
			"c-att-org-faction" => '<clan>Clan<end>',
			'att-name' => 'Nady',
			'c-att-name' => '<highlight>Nady<end>',
			'att-level' => 220,
			'c-att-level' => '<highlight>220<end>',
			'att-ai-level' => 30,
			'c-att-ai-level' => '<green>30<end>',
			'att-prof' => 'Bureaucrat',
			'c-att-prof' => '<highlight>Bureaucrat<end>',
			'att-profession' => 'Bureaucrat',
			'c-att-profession' => '<highlight>Bureaucrat<end>',
			'att-org-rank' => 'Advisor',
			'c-att-org-rank' => '<highlight>Advisor<end>',
			'att-gender' => 'Female',
			'c-att-gender' => '<highlight>Female<end>',
			'att-breed' => 'Nano',
			'c-att-breed' => '<highlight>Nano<end>',
			'att-faction' => 'Clan',
			'c-att-faction' => '<clan>Clan<end>',
			"def-org" => "Troet",
			"c-def-org" => "<neutral>Troet<end>",
			"def-faction" => "Neutral",
			"c-def-faction" => "<neutral>Neutral<end>",
			"att-coord-x" => 700,
			"att-coord-y" => 800,
			// ...SiteUpdate::EXAMPLE_TOKENS,
			'site-pf-id' => 660,
			'site-id' => 6,
			'site-nr' => 6,
			'site-number' => 6,
			'site-enabled' => 1,
			'site-min-ql' => 20,
			'site-max-ql' => 30,
			'site-name' => 'Charred Groove',
			'site-num-conductors' => 0,
			'site-num-turrets' => 5,
			'site-num-cts' => 1,
			'site-gas' => '75%',
			'c-site-gas' => '<red>75%<end>',
			'site-faction' => 'Neutral',
			'c-site-faction' => '<neutral>Neutral<clan>',
			'site-org-id' => 1,
			'site-org-name' => 'Troet',
			'c-site-org-name' => '<neutral>Troet<end>',
			'site-plant-time' => '13-Jan-2023 17:07 UTC',
			'site-ct-ql' => 25,
			// ...Playfield::EXAMPLE_TOKENS,
			"pf-id" => 551,
			"pf-long" => "Wailing Wastes",
			"pf-short" => "WW",
		],
	)]
	public string $towerAttackFormat = self::ATT_FMT_NORMAL;

	/** Display format for tower victories */
	#[NCA\Setting\Template(
		options: [
			self::VICTORY_FMT_NORMAL,
		],
		exampleValues: [
			// ...TowerOutcome::EXAMPLE_TOKENS,
			"pf-id" => 551,
			"timestamp" => "11-Mar-2023 20:12 UTC",
			"winning-faction" => "Neutral",
			"c-winning-faction" => "<neutral>Neutral<end>",
			"winning-org" => "Troet",
			"c-winning-org" => "<neutral>Troet<end>",
			"losing-faction" => "Clan",
			"c-losing-faction" => "<clan>Clan<end>",
			"losing-org" => "Team Rainbow",
			"c-losing-org" => "<clan>Team Rainbow<end>",
			// ...SiteUpdate::EXAMPLE_TOKENS,
			'site-pf-id' => 660,
			'site-id' => 6,
			'site-nr' => 6,
			'site-number' => 6,
			'site-enabled' => 1,
			'site-min-ql' => 20,
			'site-max-ql' => 30,
			'site-name' => 'Charred Groove',
			'site-num-conductors' => 0,
			'site-num-turrets' => 5,
			'site-num-cts' => 1,
			'site-gas' => '75%',
			'c-site-gas' => '<red>75%<end>',
			'site-faction' => 'Neutral',
			'c-site-faction' => '<neutral>Neutral<clan>',
			'site-org-id' => 1,
			'site-org-name' => 'Troet',
			'c-site-org-name' => '<neutral>Troet<end>',
			'site-plant-time' => '13-Jan-2023 17:07 UTC',
			'site-ct-ql' => 25,
			// ...Playfield::EXAMPLE_TOKENS,
			"pf-long" => "Wailing Wastes",
			"pf-short" => "WW",
		],
		help: 'tower_victory_format.txt',
	)]
	public string $towerVictoryFormat = self::VICTORY_FMT_NORMAL;

	/** Display format for towers being abandoned */
	#[NCA\Setting\Template(
		options: [
			self::ABANDONED_FMT_NORMAL,
			"{c-losing-org} abandoned their field at <highlight>{pf-short} {site-id}<end>",
		],
		exampleValues: [
			// ...TowerOutcome::EXAMPLE_ABANDON_TOKENS,
			"pf-id" => 551,
			"timestamp" => "11-Mar-2023 20:12 UTC",
			"winning-faction" => "Neutral",
			"c-winning-faction" => "<neutral>Neutral<end>",
			"winning-org" => "Troet",
			"c-winning-org" => "<neutral>Troet<end>",
			"losing-faction" => "Clan",
			"c-losing-faction" => "<clan>Clan<end>",
			"losing-org" => "Team Rainbow",
			"c-losing-org" => "<clan>Team Rainbow<end>",
			// ...SiteUpdate::EXAMPLE_TOKENS,
			'site-pf-id' => 660,
			'site-id' => 6,
			'site-nr' => 6,
			'site-number' => 6,
			'site-enabled' => 1,
			'site-min-ql' => 20,
			'site-max-ql' => 30,
			'site-name' => 'Charred Groove',
			'site-num-conductors' => 0,
			'site-num-turrets' => 5,
			'site-num-cts' => 1,
			'site-gas' => '75%',
			'c-site-gas' => '<red>75%<end>',
			'site-faction' => 'Neutral',
			'c-site-faction' => '<neutral>Neutral<clan>',
			'site-org-id' => 1,
			'site-org-name' => 'Troet',
			'c-site-org-name' => '<neutral>Troet<end>',
			'site-plant-time' => '13-Jan-2023 17:07 UTC',
			'site-ct-ql' => 25,
			// ...Playfield::EXAMPLE_TOKENS,
			"pf-long" => "Wailing Wastes",
			"pf-short" => "WW",
		],
		help: 'site_abandoned_format.txt',
	)]
	public string $siteAbandonedFormat = self::ABANDONED_FMT_NORMAL;

	/** Group tower attacks by site, owner and hot-phase */
	#[NCA\Setting\Boolean]
	public bool $groupTowerAttacks = true;

	#[NCA\Event("tower-attack-info", "Announce tower attacks")]
	public function announceTowerAttack(TowerAttackInfo $event): void {
		if ($event->site === null) {
			$this->logger->error("ERROR! Could not find closest site for attack");
			return;
		}
		$site = $event->site;
		$pf = $this->pfCtrl->getPlayfieldById($site->playfield_id);
		if (!isset($pf)) {
			return;
		}
		$this->logger->info("Site being attacked: {$pf->short_name} {$site->site_id}");


		$details = $this->renderAttackInfo($event, $pf);
		$shortSite = "{$pf->short_name} {$site->site_id}";
		$detailsLink = $this->text->makeBlob(
			$shortSite,
			$details,
			"Attack on {$shortSite}",
		);

		/** @var array<string,int|string|null> */
		$tokens = array_merge(
			$event->attack->getTokens(),
			$pf->getTokens(),
			$site->getTokens(),
		);

		$msg = $this->text->renderPlaceholders(
			$this->towerAttackFormat,
			$tokens
		);
		$msg = $this->text->blobWrap("{$msg} [", $detailsLink, "]");

		foreach ($msg as $page) {
			if (isset($site->org_id) && $this->config->orgId === $site->org_id) {
				$rMsg = new RoutableMessage($page);
				$rMsg->prependPath(new Source('pvp', "tower-attack-own"));
				$this->msgHub->handle($rMsg);
			}
			$rMsg = new RoutableMessage($page);
			$rMsg->prependPath(new Source('pvp', "tower-attack"));
			$this->msgHub->handle($rMsg);
			$this->siteTracker->fireEvent(new RoutableMessage($page), $site, 'tower-attack');
		}
	}

	#[NCA\Event("tower-outcome", "Announce tower victories and abandoned sites")]
	public function announceTowerVictories(Event\TowerOutcome $event): void {
		$outcome = $event->outcome;
		$pf = $this->pfCtrl->getPlayfieldById($outcome->playfield_id);
		$site = $this->nwCtrl->state[$outcome->playfield_id][$outcome->site_id];
		if (!isset($pf) || !isset($site)) {
			$this->logger->error("Cannot find site at {pf} {siteId}", [
				"pf" => $outcome->playfield_id,
				"siteId" => $outcome->site_id,
			]);
			return;
		}
		$site->ct_pos = null;
		$site->num_conductors = 0;
		$site->num_turrets = 0;
		$site->org_faction = null;
		$site->org_id = null;
		$site->org_name = null;
		$site->plant_time = null;
		$site->ql = null;

		/** @var array<string,string|int|null> */
		$tokens = array_merge(
			$outcome->getTokens(),
			$site->getTokens(),
			$pf->getTokens(),
		);
		$format = isset($tokens["winning-faction"])
			? $this->towerVictoryFormat
			: $this->siteAbandonedFormat;
		$msg = $this->text->renderPlaceholders($format, $tokens);

		$details = $this->nwCtrl->renderSite($site, $pf, false, true, $outcome);
		$shortSite = "{$pf->short_name} {$site->site_id}";
		$detailsLink = $this->text->makeBlob(
			$shortSite,
			$details,
		);
		$msg = $this->text->blobWrap("{$msg} [", $detailsLink, "]");

		foreach ($msg as $page) {
			if (isset($site->org_id) && $this->config->orgId === $site->org_id) {
				$rMsg = new RoutableMessage($page);
				$rMsg->prependPath(new Source('pvp', "tower-outcome-own"));
				$this->msgHub->handle($rMsg);
			}
			$rMsg = new RoutableMessage($page);
			$rMsg->prependPath(new Source("pvp", "tower-outcome"));
			$this->msgHub->handle($rMsg);
			$this->siteTracker->fireEvent(new RoutableMessage($page), $site, 'tower-outcome');
		}
	}

	/** Show the last tower attack messages */
	#[NCA\HandlesCommand(self::CMD_ATTACKS)]
	public function nwAttacksAnywhereCommand(
		CmdContext $context,
		#[NCA\Str("attacks")] string $action,
		?int $page,
	): void {
		$query = $this->db->table($this->nwCtrl::DB_ATTACKS);
		$context->reply($this->nwAttacksCmd(
			$query,
			"Tower Attacks",
			"nw attacks",
			$page??1,
			null,
		));
	}

	/** Show the last tower attack messages for a site */
	#[NCA\HandlesCommand(self::CMD_ATTACKS)]
	public function nwAttacksForSiteCommand(
		CmdContext $context,
		#[NCA\Str("attacks")] string $action,
		PTowerSite $towerSite,
		?int $page,
	): void {
		$pf = $this->pfCtrl->getPlayfieldByName($towerSite->pf);
		if (!isset($pf)) {
			$msg = "Playfield <highlight>{$towerSite->pf}<end> could not be found.";
			$context->reply($msg);
			return;
		}
		$query = $this->db->table($this->nwCtrl::DB_ATTACKS)
			->where("playfield_id", $pf->id)
			->where("site_id", $towerSite->site);
		$context->reply($this->nwAttacksCmd(
			$query,
			"Tower Attacks on {$pf->short_name} {$towerSite->site}",
			"nw attacks",
			$page??1,
			false,
		));
	}

	/**
	 * Show the last tower attack messages involving a specific organization
	 *
	 * You can use '*' as a wildcard in org names
	 */
	#[NCA\HandlesCommand(self::CMD_ATTACKS)]
	#[NCA\Help\Example("<symbol>nw attacks org *sneak*")]
	#[NCA\Help\Example("<symbol>nw attacks org Komodo")]
	public function nwAttacksForOrgCommand(
		CmdContext $context,
		#[NCA\Str("attacks")] string $action,
		#[NCA\Str("org")] string $org,
		PNonGreedy $orgName,
		?int $page,
	): void {
		$search = str_replace("*", "%", $orgName());
		$query = $this->db->table($this->nwCtrl::DB_ATTACKS)
			->whereIlike("att_org", $search)
			->orWhereIlike("def_org", $search);
		$context->reply(
			$this->nwAttacksCmd(
				$query,
				"Tower Attacks on/by '{$orgName}'",
				"nw attacks org {$orgName}",
				$page??1,
				false,
			)
		);
	}

	/**
	 * Show the last tower attack messages involving a given character
	 *
	 * You can use '*' as a wildcard in character names
	 */
	#[NCA\HandlesCommand(self::CMD_ATTACKS)]
	#[NCA\Help\Example("<symbol>nw attacks char nady*")]
	#[NCA\Help\Example("<symbol>nw attacks char nadyita")]
	public function nwAttacksForCharCommand(
		CmdContext $context,
		#[NCA\Str("attacks")] string $action,
		#[NCA\Str("char")] string $char,
		PNonGreedy $search,
		?int $page,
	): void {
		$query = $this->db->table($this->nwCtrl::DB_ATTACKS)
			->whereIlike("att_name", str_replace('*', '%', $search()));
		$context->reply(
			$this->nwAttacksCmd(
				$query,
				"Tower Attacks by '{$search}'",
				"nw attacks char {$search}",
				$page??1,
				false,
			)
		);
	}

	/** Show the last tower victories */
	#[NCA\HandlesCommand(self::CMD_OUTCOMES)]
	public function nwOutcomesAnywhereCommand(
		CmdContext $context,
		#[NCA\Str("victory")] string $action,
		?int $page,
	): void {
		$query = $this->db->table($this->nwCtrl::DB_OUTCOMES);
		$context->reply($this->nwOutcomesCmd(
			$query,
			"Tower Victories",
			"nw victory",
			$page??1,
		));
	}

	/** Show the last tower victories for a site */
	#[NCA\HandlesCommand(self::CMD_OUTCOMES)]
	public function nwOutcomesForSiteCommand(
		CmdContext $context,
		#[NCA\Str("victory")] string $action,
		PTowerSite $towerSite,
		?int $page,
	): void {
		$pf = $this->pfCtrl->getPlayfieldByName($towerSite->pf);
		if (!isset($pf)) {
			$msg = "Playfield <highlight>{$towerSite->pf}<end> could not be found.";
			$context->reply($msg);
			return;
		}
		$query = $this->db->table($this->nwCtrl::DB_OUTCOMES)
			->where("playfield_id", $pf->id)
			->where("site_id", $towerSite->site);
		$context->reply($this->nwOutcomesCmd(
			$query,
			"Tower Victories on {$pf->short_name} {$towerSite->site}",
			"nw victory",
			$page??1,
		));
	}

	/**
	 * Show the last tower victories involving a specific organization
	 *
	 * You can use '*' as a wildcard in org names
	 */
	#[NCA\HandlesCommand(self::CMD_ATTACKS)]
	#[NCA\Help\Example("<symbol>nw victory org *sneak*")]
	#[NCA\Help\Example("<symbol>nw victory org Komodo")]
	public function nwOutcomesForOrgCommand(
		CmdContext $context,
		#[NCA\Str("victory")] string $action,
		#[NCA\Str("org")] string $org,
		PNonGreedy $orgName,
		?int $page,
	): void {
		$search = str_replace("*", "%", $orgName());
		$query = $this->db->table($this->nwCtrl::DB_OUTCOMES)
			->whereIlike("attacker_org", $search)
			->orWhereIlike("losing_org", $search);
		$context->reply(
			$this->nwOutcomesCmd(
				$query,
				"Tower Attacks on/by '{$orgName}'",
				"nw attacks org {$orgName}",
				$page??1
			)
		);
	}

	private function renderAttackInfo(TowerAttackInfo $info, Playfield $pf): string {
		$attack = $info->attack;
		$attacker = $attack->attacker;
		$site = $info->site;
		assert(isset($site));
		$blob = "<header2>Attacker<end>\n";
		$blob .= "<tab>Name: <highlight>{$attacker->name}<end>\n";
		if (isset($attacker->breed) && strlen($attacker->breed)) {
			$blob .= "<tab>Breed: <highlight>{$attacker->breed}<end>\n";
		}
		if (isset($attacker->gender) && strlen($attacker->gender)) {
			$blob .= "<tab>Gender: <highlight>{$attacker->gender}<end>\n";
		}

		if (isset($attacker->profession) && strlen($attacker->profession)) {
			$blob .= "<tab>Profession: <highlight>{$attacker->profession}<end>\n";
		}
		if (isset($attacker->level, $attacker->ai_level)) {
			$level_info = $this->lvlCtrl->getLevelInfo($attacker->level);
			if (isset($level_info)) {
				$blob .= "<tab>Level: <highlight>{$attacker->level}/<green>{$attacker->ai_level}<end> ({$level_info->pvpMin}-{$level_info->pvpMax})<end>\n";
			}
		}

		$attFaction = $attacker->faction ?? $attacker->org?->faction;
		if (isset($attFaction)) {
			$blob .= "<tab>Alignment: <" . strtolower($attFaction) . ">{$attFaction}<end>\n";
		}

		if (isset($attacker->org)) {
			$blob .= "<tab>Organization: <highlight>{$attacker->org->name}<end>\n";
			if (isset($attacker->org_rank)) {
				$blob .= "<tab>Organization Rank: <highlight>{$attacker->org_rank}<end>\n";
			}
		}

		$blob .= "\n";

		$blob .= "<header2>Defender<end>\n";
		$blob .= "<tab>Organization: <highlight>{$attack->defender->name}<end>\n";
		$blob .= "<tab>Alignment: <" . strtolower($attack->defender->faction) . ">{$attack->defender->faction}<end>\n\n";

		$baseLink = $this->text->makeChatcmd("{$pf->short_name} {$site->site_id}", "/tell <myname> nw lc {$pf->short_name} {$site->site_id}");
		$attackWaypoint = $this->text->makeChatcmd(
			"{$attack->location->x}x{$attack->location->y}",
			"/waypoint {$attack->location->x} {$attack->location->y} {$pf->id}"
		);
		$blob .= "<header2>Waypoints<end>\n";
		$blob .= "<tab>Site: <highlight>{$baseLink} ({$site->min_ql}-{$site->max_ql})<end>\n";
		$blob .= "<tab>Attack: <highlight>{$pf->long_name} ({$attackWaypoint})<end>\n";

		return $blob;
	}

	private function renderDBOutcome(DBOutcome $outcome, FeedMessage\SiteUpdate $site, Playfield $pf): string {
		$blob = "Time: " . $this->util->date($outcome->timestamp) . " (".
			"<highlight>" . $this->util->unixtimeToReadable(time() - $outcome->timestamp).
			"<end> ago)\n";
		if (isset($outcome->attacker_org, $outcome->attacker_faction)) {
			$blob .= "Winner: <" . strtolower($outcome->attacker_faction) . ">".
				$outcome->attacker_org . "<end>\n";
		} else {
			$blob .= "Winner: <grey>abandoned<end>\n";
		}
		$blob .= "Loser: <" . strtolower($outcome->losing_faction) . ">".
			$outcome->losing_org . "<end>\n";
		$siteLink = $this->text->makeChatcmd(
			"{$pf->short_name} {$site->site_id}",
			"/tell <myname> <symbol>nw lc {$pf->short_name} {$site->site_id}"
		);
		$blob .= "Site: {$siteLink} (QL {$site->min_ql}-{$site->max_ql})";

		return $blob;
	}

	/** @return string[] */
	private function nwAttacksCmd(QueryBuilder $query, string $title, string $command, int $page, ?bool $group): array {
		$numAttacks = $query->count();
		$attacks = $query
			->orderByDesc("timestamp")
			->limit(15)
			->offset(($page-1) * 15)
			->asObj(DBTowerAttack::class);
		return $this->renderAttackList(
			$command,
			$page,
			$numAttacks,
			$title,
			$group,
			...$attacks->toArray()
		);
	}

	/** @return string[] */
	private function nwOutcomesCmd(QueryBuilder $query, string $title, string $command, int $page): array {
		$numOutcomes = $query->count();
		$attacks = $query
			->orderByDesc("timestamp")
			->limit(15)
			->offset(($page-1) * 15)
			->asObj(DBOutcome::class);
		return $this->renderOutcomeList(
			$command,
			$page,
			$numOutcomes,
			$title,
			...$attacks->toArray()
		);
	}

	/**
	 * Group a given list of attacks into attack-phases divided
	 * by victories and 75% phases
	 *
	 * @return Collection<string,Collection<DBTowerAttack>>
	 */
	private function groupAttackList(DBTowerAttack ...$attacks): Collection {
		/** @var Collection<DBTowerAttack> */
		$attacks = (new Collection($attacks))->sortByDesc("timestamp");

		/**
		 * A hash with site/owner key and a list of outcomes for this combination
		 *
		 * @var array<string,DBOutcome[]>
		 */
		$outcomes = $this->db->table($this->nwCtrl::DB_OUTCOMES)
			->where("timestamp", ">", $attacks->last()->timestamp)
			->where("timestamp", "<", $attacks->first()->timestamp)
			->orderByDesc("timestamp")
			->asObj(DBOutcome::class)
			->groupBy(function (DBOutcome $outcome): string {
				return "{$outcome->losing_org}:{$outcome->playfield_id}:{$outcome->site_id}";
			})->toArray();

		/**
		 * A hash with site/owner key and a list of attacks for this combination
		 *
		 * @var array<string,DBTowerAttack[]>
		 */
		$groups = $attacks
			->reduce(function (array $groups, DBTowerAttack $attack): array {
				$key = "{$attack->def_org}:{$attack->playfield_id}:{$attack->site_id}";
				$groups[$key] ??= [];
				$groups[$key] []= $attack;
				return $groups;
			}, []);
		$lookup = [];
		foreach ($groups as $key => $gAttacks) {
			/** @var DBOutcome[] */
			$keyOutcomes = $outcomes[$key] ?? [];
			$lastAttack = null;
			$id = 0;
			$lastOutcome = array_shift($keyOutcomes);
			foreach ($gAttacks as $attack) {
				if (isset($lastOutcome) && $lastOutcome->timestamp > $attack->timestamp) {
					$id++;
					$lastOutcome = array_shift($keyOutcomes);
				} elseif (!isset($lastAttack) || abs($lastAttack->timestamp - $attack->timestamp) > 6*3600) {
					$id++;
				}
				$lookup["{$key}:{$attack->timestamp}"] = $id;
				$lastAttack = $attack;
			}
		}

		$grouped = (new Collection($attacks))->groupBy(
			function (DBTowerAttack $attack) use ($lookup): string {
				$key = "{$attack->def_org}:{$attack->playfield_id}:{$attack->site_id}";
				return $key . ':' . $lookup["{$key}:{$attack->timestamp}"];
			}
		);
		return $grouped;
	}

	/** @return string[] */
	private function renderAttackList(
		string $baseCommand,
		int $page,
		int $numAttacks,
		string $title,
		?bool $groupTowerAttacks,
		DBTowerAttack ...$attacks
	): array {
		if (empty($attacks)) {
			return ["No tower attacks found."];
		}
		$groupTowerAttacks ??= $this->groupTowerAttacks;
		if ($groupTowerAttacks) {
			$groups = $this->groupAttackList(...$attacks);
			$blocks = $groups->map(function (Collection $attacks): string {
				/** @var DBTowerAttack */
				$first = $attacks->firstOrFail();

				/** @var ?DBTowerAttack */
				$last = $attacks->last();
				$pf = $this->pfCtrl->getPlayfieldById($first->playfield_id);
				$site = $this->nwCtrl->state[$first->playfield_id][$first->site_id] ?? null;
				assert(isset($last));
				assert(isset($pf));
				assert(isset($site));

				/** @var ?DBOutcome */
				$outcome = $this->db->table($this->nwCtrl::DB_OUTCOMES)
					->where("losing_org", $first->def_org)
					->where("timestamp", ">", $last->timestamp)
					->where("timestamp", "<", $last->timestamp + 6 * 3600)
					->where("playfield_id", $site->playfield_id)
					->where("site_id", $site->site_id)
					->whereNotNull("attacker_org")
					->orderBy("timestamp")
					->limit(1)
					->asObj(DBOutcome::class)
					->first();

				$blocks = [];

				/** @var DBTowerAttack[] $attacks */
				foreach ($attacks as $attack) {
					$blocks []= $this->util->date($attack->timestamp) . ": ".
						$this->renderDBAttacker($attack);
				}
				$defColor = strtolower($first->def_faction);
				return "<header2>{$pf->short_name} {$first->site_id}<end>".
					" (QL {$site->min_ql}-{$site->max_ql}) [".
					$this->text->makeChatcmd(
						"details",
						"/tell <myname> <symbol>nw lc {$pf->short_name} {$first->site_id}"
					) . "]\n".
					"<tab>Defender: <{$defColor}>{$first->def_org}<end>\n".
					(
						isset($outcome, $outcome->attacker_faction, $outcome->attacker_org)
							? "<tab>Won by <" . strtolower($outcome->attacker_faction) . ">".
								$outcome->attacker_org . "<end> at ".
								$this->util->date($outcome->timestamp) . "\n\n"
							: "\n"
					) . "<tab>".
					join("\n<tab>", $blocks);
			})->toArray();
		} else {
			$blocks = [];
			foreach ($attacks as $attack) {
				$blocks []= $this->renderDBAttack($attack);
			}
		}
		$prevLink = "&lt;&lt;&lt;";
		if ($page > 1) {
			$prevLink = $this->text->makeChatcmd(
				$prevLink,
				"/tell <myname> {$baseCommand} " . ($page-1)
			);
		}
		$nextLink = "";
		if ($page * 15 < $numAttacks) {
			$nextLink = $this->text->makeChatcmd(
				"&gt;&gt;&gt;",
				"/tell <myname> {$baseCommand} " . ($page+1)
			);
		}
		$blob = "";
		if ($numAttacks > 15) {
			$blob = "{$prevLink}<tab>Page {$page}<tab>{$nextLink}\n\n";
		}
		$blob .= join("\n\n", $blocks);
		$msg = $this->text->makeBlob(
			$title,
			$blob
		);
		return (array)$msg;
	}

	/** @return string[] */
	private function renderOutcomeList(
		string $baseCommand,
		int $page,
		int $numAttacks,
		string $title,
		DBOutcome ...$outcomes
	): array {
		if (empty($outcomes)) {
			return ["No tower victories found."];
		}
		$blocks = [];
		foreach ($outcomes as $outcome) {
			$pf = $this->pfCtrl->getPlayfieldById($outcome->playfield_id);
			$site = $this->nwCtrl->state[$outcome->playfield_id][$outcome->site_id] ?? null;
			if (!isset($pf) || !isset($site)) {
				continue;
			}
			$blocks []= $this->renderDBOutcome($outcome, $site, $pf);
		}
		$prevLink = "&lt;&lt;&lt;";
		if ($page > 1) {
			$prevLink = $this->text->makeChatcmd(
				$prevLink,
				"/tell <myname> {$baseCommand} " . ($page-1)
			);
		}
		$nextLink = "";
		if ($page * 15 < $numAttacks) {
			$nextLink = $this->text->makeChatcmd(
				"&gt;&gt;&gt;",
				"/tell <myname> {$baseCommand} " . ($page+1)
			);
		}
		$blob = "";
		if ($numAttacks > 15) {
			$blob = "{$prevLink}<tab>Page {$page}<tab>{$nextLink}\n\n";
		}
		$blob .= join("\n\n", $blocks);
		$msg = $this->text->makeBlob(
			$title,
			$blob
		);
		return (array)$msg;
	}

	/** Render info about an attacker for !nw attacks */
	private function renderDBAttacker(DBTowerAttack $attack): string {
		$attColor = strtolower($attack->att_faction ?? "Unknown");
		$blob = "<{$attColor}>{$attack->att_name}<end>";
		if (isset($attack->att_level, $attack->att_ai_level, $attack->att_profession)) {
			$blob .= " ({$attack->att_level}/<green>{$attack->att_ai_level}<end>";
			if (isset($attack->att_gender)) {
				$blob .= ", {$attack->att_gender}";
			}
			if (isset($attack->att_breed)) {
				$blob .= " {$attack->att_breed}";
			}
			$blob .= " <highlight>{$attack->att_profession}<end>";
			if (isset($attack->att_org_rank)) {
				$blob .= ", {$attack->att_org_rank}";
			}
			if (isset($attack->att_org)) {
				$blob .= " of <{$attColor}>{$attack->att_org}<end>)";
			} else {
				$blob .= ")";
			}
		} elseif (isset($attack->att_org)) {
			$blob .= " <{$attColor}>{$attack->att_org}<end>";
		}
		return $blob;
	}

	/** Render a single, ungrouped !nw attacks line */
	private function renderDBAttack(DBTowerAttack $attack): string {
		$defColor = strtolower($attack->def_faction);
		$blob = "Time: " . $this->util->date($attack->timestamp).
			" (<highlight>".
			$this->util->unixtimeToReadable(time() - $attack->timestamp).
			"<end> ago)\n";
		$blob .= "Attacker: " . $this->renderDBAttacker($attack) . "\n";
		$blob .= "Defender: <{$defColor}>{$attack->def_org}<end>";
		$site = $this->nwCtrl->state[$attack->playfield_id][$attack->site_id] ?? null;
		$pf = $this->pfCtrl->getPlayfieldById($attack->playfield_id);
		if (isset($site, $pf)) {
			$blob .= "\nSite: " . $this->text->makeChatcmd(
				"{$pf->short_name} {$attack->site_id}",
				"/tell <myname> nw lc {$pf->short_name} {$attack->site_id}"
			) . " (QL {$site->min_ql}-{$site->max_ql})";
		}
		return $blob;
	}
}