<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE;

use function Safe\{json_decode, json_encode, preg_match};

use Amp\File\{FilesystemException};
use Closure;
use EventSauce\ObjectHydrator\{DefinitionProvider, KeyFormatterWithoutConversion, ObjectMapperUsingReflection, UnableToHydrateObject};
use Exception;
use Nadybot\Core\DBSchema\{Admin, Alt, BanEntry, Member};
use Nadybot\Core\{
	AccessManager,
	AdminManager,
	Attributes as NCA,
	CmdContext,
	Config\BotConfig,
	DB,
	Filesystem,
	ModuleInstance,
	Modules\BAN\BanController,
	Modules\PREFERENCES\Preferences,
	Nadybot,
	ParamClass\PFilename,
	Safe,
	SettingManager,
	Util,
};
use Nadybot\Modules\CITY_MODULE\OrgCity;
use Nadybot\Modules\EVENTS_MODULE\EventModel;
use Nadybot\Modules\EXPORT_MODULE\Schema\{AltChar, AltMain};
use Nadybot\Modules\GUILD_MODULE\OrgMember;
use Nadybot\Modules\NEWS_MODULE\{News, NewsConfirmed};
use Nadybot\Modules\NOTES_MODULE\{Link, OrgNote};
use Nadybot\Modules\QUOTE_MODULE\Quote;
use Nadybot\Modules\RAFFLE_MODULE\RaffleBonus;
use Nadybot\Modules\RAID_MODULE\{DBAuction, RaidBlock, RaidPointsLog, RaidRank};
use Nadybot\Modules\TRACKER_MODULE\{TrackedUser, Tracking};
use Nadybot\Modules\VOTE_MODULE\{Poll, Vote};
use Nadybot\Modules\{
	COMMENT_MODULE\Comment,
	COMMENT_MODULE\CommentCategory,
	COMMENT_MODULE\CommentController,
	MASSMSG_MODULE\MassMsgController,
	NOTES_MODULE\Note,
	RAID_MODULE\Raid,
	RAID_MODULE\RaidLog,
	RAID_MODULE\RaidMember,
	RAID_MODULE\RaidPoints,
	RAID_MODULE\RaidRankController,
	TIMERS_MODULE\Alert,
	TIMERS_MODULE\Timer,
	VOTE_MODULE\VoteController,
};
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @author Nadyita (RK5) <nadyita@hodorraid.org>
 */
#[
	NCA\Instance,
	NCA\DefineCommand(
		command: 'import',
		accessLevel: 'superadmin',
		description: 'Import bot data and replace the current one',
	)
]
class ImportController extends ModuleInstance {
	#[NCA\Logger]
	private LoggerInterface $logger;

	#[NCA\Inject]
	private Nadybot $chatBot;

	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Filesystem $fs;

	#[NCA\Inject]
	private Preferences $preferences;

	#[NCA\Inject]
	private AdminManager $adminManager;

	#[NCA\Inject]
	private AccessManager $accessManager;

	#[NCA\Inject]
	private BanController $banController;

	#[NCA\Inject]
	private SettingManager $settingManager;

	#[NCA\Inject]
	private CommentController $commentController;

	#[NCA\Inject]
	private RaidRankController $raidRankController;

	#[NCA\Inject]
	private BotConfig $config;

	/** Import data from a file, mapping the exported access levels to your own ones */
	#[NCA\HandlesCommand('import')]
	#[NCA\Help\Example('<symbol>import 2021-01-31 superadmin=admin admin=mod leader=member member=member')]
	#[NCA\Help\Prologue(
		"In order to import data from an old export, you should first think about\n".
		"how you want to map access levels between the bots.\n".
		'BeBot or Tyrbot use a totally different access level system than Nadybot.'
	)]
	#[NCA\Help\Epilogue(
		"<header2>Warning<end>\n\n".
		"Please note that importing a dump will delete most of the already existing\n".
		"data of your bot, so:\n".
		"<highlight>only do this after you created an export or database backup<end>!\n".
		"This cannot be stressed enough.\n\n".
		"<header2>In detail<end>\n\n".
		"Everything that is included in the dump, will be deleted before importing.\n".
		"So if your dump contains members of the bot, they will all be wiped first.\n".
		"If it does include an empty set of members, they will still be wiped.\n".
		"Only if the members were not exported at all, they won't be touched.\n\n".
		"There is no extra step in-between, so be careful not to delete any\n".
		"data you might want to keep.\n"
	)]
	public function importCommand(
		CmdContext $context,
		PFilename $file,
		#[NCA\Regexp("\w+=\w+", example: '&lt;exported al&gt;=&lt;new al&gt;')] ?string ...$mappings
	): void {
		$dataPath = $this->config->paths->data;
		$fileName = "{$dataPath}/export/" . basename($file());
		if ((pathinfo($fileName)['extension'] ?? '') !== 'json') {
			$fileName .= '.json';
		}
		if (!$this->fs->exists($fileName)) {
			$context->reply("No export file <highlight>{$fileName}<end> found.");
			return;
		}
		$import = $this->loadAndParseExportFile($fileName, $context);
		if (!isset($import)) {
			return;
		}
		$usedRanks = $this->getRanks($import);
		$rankMapping = $this->parseRankMapping(array_filter($mappings));
		foreach ($usedRanks as $rank) {
			if (!isset($rankMapping[$rank])) {
				$context->reply("Please define a mapping for <highlight>{$rank}<end> by appending '{$rank}=&lt;rank&gt;' to your command");
				return;
			}
			try {
				$rankMapping[$rank] = $this->accessManager->getAccessLevel($rankMapping[$rank]);
			} catch (Exception) {
				$context->reply("<highlight>{$rankMapping[$rank]}<end> is not a valid access level");
				return;
			}
		}
		$this->logger->notice('Starting import');
		$context->reply('Starting import...');
		$importMap = $this->getImportMapping();
		foreach ($importMap as $key => $func) {
			if (!isset($import->{$key})) {
				continue;
			}
			$func($import->{$key}, $rankMapping);
		}
		$this->logger->notice('Import done');
		$context->reply('The import finished successfully.');
	}

	/** @param list<Schema\Auction> $auctions */
	public function importAuctions(array $auctions): void {
		$this->logger->notice('Importing {num_auctions} auction(s)', [
			'num_auctions' => count($auctions),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all auctions');
			$this->db->table(DBAuction::getTable())->truncate();
			foreach ($auctions as $auction) {
				$this->db->insert(new DBAuction(
					item: $auction->item,
					auctioneer: ($this->characterToName($auction->startedBy??null)) ?? $this->config->main->character,
					cost: (0 !== ($auction->cost ?? 0)) ? (int)round($auction->cost??0, 0) : null,
					winner: $this->characterToName($auction->winner??null),
					end: $auction->timeEnd ?? time(),
					reimbursed: $auction->reimbursed ?? false,
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All auctions imported');
	}

	/** @param list<Schema\Ban> $banlist */
	public function importBanlist(array $banlist): void {
		$numImported = 0;
		$this->logger->notice('Importing {num_bans} ban(s)', [
			'num_bans' => count($banlist),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all bans');
			$this->db->table(BanEntry::getTable())->truncate();
			foreach ($banlist as $ban) {
				/** @psalm-suppress PossiblyNullArgument */
				$id = $ban->character->id ?? $this->chatBot->getUid($ban->character->name);
				if (!isset($id)) {
					continue;
				}
				$this->db->insert(new BanEntry(
					charid: $id,
					admin: ($this->characterToName($ban->bannedBy ?? null)) ?? $this->config->main->character,
					time: $ban->banStart ?? time(),
					reason: $ban->banReason ?? 'None given',
					banend: $ban->banEnd ?? 0,
				));
				$numImported++;
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->banController->uploadBanlist();
		$this->logger->notice('{num_imported} bans successfully imported', [
			'num_imported' => $numImported,
		]);
	}

	/** @param list<Schema\CloakEntry> $cloakActions */
	public function importCloak(array $cloakActions): void {
		$this->logger->notice('Importing {num_actions} cloak action(s)', [
			'num_actions' => count($cloakActions),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all cloak actions');
			$this->db->table(OrgCity::getTable())->truncate();
			foreach ($cloakActions as $action) {
				$this->db->insert(new OrgCity(
					time: $action->time,
					action: $action->cloakOn ? 'on' : 'off',
					player: ($this->characterToName($action->character??null)) ?? $this->config->main->character,
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All cloak actions imported');
	}

	/** @param list<Schema\Link> $links */
	public function importLinks(array $links): void {
		$this->logger->notice('Importing {num_links} links', [
			'num_links' => count($links),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all links');
			$this->db->table(Link::getTable())->truncate();
			foreach ($links as $link) {
				$this->db->insert(new Link(
					name: ($this->characterToName($link->createdBy??null)) ?? $this->config->main->character,
					website: $link->url,
					comments: $link->description ?? '',
					dt: $link->creationTime ?? null,
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All links imported');
	}

	/**
	 * @param list<Schema\Member>  $members
	 * @param array<string,string> $rankMap
	 */
	public function importMembers(array $members, array $rankMap=[]): void {
		$numImported = 0;
		$this->logger->notice('Importing {num_members} member(s)', [
			'num_members' => count($members),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all members');
			$this->db->table(Member::getTable())->truncate();
			$this->db->table(OrgMember::getTable())->truncate();
			$this->db->table(Admin::getTable())->truncate();
			$this->db->table(RaidRank::getTable())->truncate();
			foreach ($members as $member) {
				/** @psalm-suppress PossiblyNullArgument */
				$id = $member->character->id ?? $this->chatBot->getUid($member->character->name);

				/** @var ?string */
				$name = $this->characterToName($member->character);
				if (!isset($id) || !isset($name)) {
					continue;
				}
				$this->chatBot->cacheUidNameMapping(name: $name, uid: $id);
				$newRank = $this->getMappedRank($rankMap, $member->rank);
				if (!isset($newRank)) {
					throw new Exception("Cannot find rank {$member->rank} in the mapping");
				}
				$numImported++;
				if (in_array($newRank, ['member', 'mod', 'admin', 'superadmin'], true)
					|| preg_match('/^raid_(leader|admin)_[123]$/', $newRank)
				) {
					$this->db->insert(new Member(
						name: $name,
						autoinv: (int)($member->autoInvite ?? false),
						joined: $member->joinedTime ?? time(),
					));
				}
				if (in_array($newRank, ['mod', 'admin', 'superadmin'], true)) {
					$adminLevel = ($newRank === 'mod') ? 3 : 4;
					$this->db->insert(new Admin(
						name: $name,
						adminlevel: $adminLevel,
					));
					$this->adminManager->admins[$name] = ['level' => $adminLevel];
				} elseif (count($matches = Safe::pregMatch('/^raid_leader_([123])/', $newRank))) {
					$this->db->insert(new Raidrank(
						name: $name,
						rank: (int)$matches[1] + 3,
					));
				} elseif (count($matches = Safe::pregMatch('/^raid_admin_([123])/', $newRank))) {
					$this->db->insert(new Raidrank(
						name: $name,
						rank: (int)$matches[1] + 6,
					));
				} elseif (in_array($newRank, ['rl', 'all'])) {
					// Nothing, we just ignore that
				}
				if (isset($member->logonMessage)) {
					$this->preferences->save($name, 'logon_msg', $member->logonMessage);
				}
				if (isset($member->logoffMessage)) {
					$this->preferences->save($name, 'logoff_msg', $member->logoffMessage);
				}
				if (isset($member->receiveMassInvites)) {
					$this->preferences->save($name, MassMsgController::PREF_INVITES, $member->receiveMassInvites ? 'on' : 'off');
				}
				if (isset($member->receiveMassMessages)) {
					$this->preferences->save($name, MassMsgController::PREF_MSGS, $member->receiveMassMessages ? 'on' : 'off');
				}
			}
			$this->raidRankController->uploadRaidRanks();
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('{num_imported} members successfully imported', [
			'num_imported' => $numImported,
		]);
	}

	/** @param list<Schema\Event> $events */
	public function importEvents(array $events): void {
		$this->logger->notice('Importing {num_events} events', [
			'num_events' => count($events),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all events');
			$this->db->table(EventModel::getTable())->truncate();
			foreach ($events as $event) {
				$attendees = [];
				foreach ($event->attendees??[] as $attendee) {
					$name = $this->characterToName($attendee);
					if (isset($name)) {
						$attendees []= $name;
					}
				}
				$this->db->insert(new EventModel(
					time_submitted: $event->creationTime ?? time(),
					submitter_name: ($this->characterToName($event->createdBy ?? null)) ?? $this->config->main->character,
					event_name: $event->name,
					event_date: $event->startTime ?? null,
					event_desc: $event->description ?? null,
					event_attendees: implode(',', $attendees),
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All events imported');
	}

	/** @param list<Schema\News> $news */
	public function importNews(array $news): void {
		$this->logger->notice('Importing {num_news} news', [
			'num_news' => count($news),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all news');
			$this->db->table(NewsConfirmed::getTable())->truncate();
			$this->db->table(News::getTable())->truncate();
			foreach ($news as $item) {
				$newsId = $this->db->insert(new News(
					time: $item->addedTime ?? time(),
					uuid: $item->uuid ?? Util::createUUID(),
					name: ($this->characterToName($item->author ?? null)) ?? $this->config->main->character,
					news: $item->news,
					sticky: $item->pinned ?? false,
					deleted: $item->deleted ?? false,
				));
				foreach ($item->confirmedBy??[] as $confirmation) {
					$name = $this->characterToName($confirmation->character??null);
					if (!isset($name)) {
						continue;
					}
					$this->db->insert(new NewsConfirmed(
						id: $newsId,
						player: $name,
						time: $confirmation->confirmationTime ?? time(),
					));
				}
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All news imported');
	}

	/** @param list<Schema\Note> $notes */
	public function importNotes(array $notes): void {
		$this->logger->notice('Importing {num_notes} notes', [
			'num_notes' => count($notes),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all notes');
			$this->db->table(Note::getTable())->truncate();
			foreach ($notes as $note) {
				$owner = $this->characterToName($note->owner??null);
				if (!isset($owner)) {
					continue;
				}
				$reminder = $note->remind ?? null;
				$reminderInt = ($reminder === 'all')
					? Note::REMIND_ALL
					: (($reminder === 'author')
						? Note::REMIND_SELF
						: Note::REMIND_NONE);
				$this->db->insert(new Note(
					owner: $owner,
					added_by: ($this->characterToName($note->author ?? null)) ?? $owner,
					note: $note->text,
					dt: $note->creationTime ?? null,
					reminder: $reminderInt,
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All notes imported');
	}

	/** @param list<Schema\OrgNote> $notes */
	public function importOrgNotes(array $notes): void {
		$this->logger->notice('Importing {num_notes} org notes', [
			'num_notes' => count($notes),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all org notes');
			$this->db->table(OrgNote::getTable())->truncate();
			foreach ($notes as $note) {
				$owner = $this->characterToName($note->author);
				if (!isset($owner)) {
					continue;
				}
				$this->db->insert(new OrgNote(
					added_by: $owner,
					note: $note->text,
					added_on: $note->creationTime ?? null,
					uuid: $note->uuid ?? Util::createUUID(),
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All org notes imported');
	}

	/** @param list<Schema\Poll> $polls */
	public function importPolls(array $polls): void {
		$this->logger->notice('Importing {num_polls} polls', [
			'num_polls' => count($polls),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all polls');
			$this->db->table(Vote::getTable())->truncate();
			$this->db->table(Poll::getTable())->truncate();
			foreach ($polls as $poll) {
				$pollId = $this->db->insert(new Poll(
					author: ($this->characterToName($poll->author)) ?? $this->config->main->character,
					question: $poll->question,
					possible_answers: json_encode(
						array_map(
							static function (Schema\Answer $answer): string {
								return $answer->answer;
							},
							$poll->answers??[]
						),
					),
					started: $poll->startTime ?? time(),
					duration: ($poll->endTime ?? time()) - ($poll->startTime ?? time()),
					status: VoteController::STATUS_STARTED,
				));
				foreach ($poll->answers??[] as $answer) {
					foreach ($answer->votes??[] as $vote) {
						$this->db->insert(new Vote(
							poll_id: $pollId,
							author: ($this->characterToName($vote->character??null)) ?? 'Unknown',
							answer: $answer->answer,
							time: $vote->voteTime ?? time(),
						));
					}
				}
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All polls imported');
	}

	/** @param list<Schema\Quote> $quotes */
	public function importQuotes(array $quotes): void {
		$this->logger->notice('Importing {num_quotes} quotes', [
			'num_quotes' => count($quotes),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all quotes');
			$this->db->table(Quote::getTable())->truncate();
			foreach ($quotes as $quote) {
				$this->db->insert(new Quote(
					poster: ($this->characterToName($quote->contributor)) ?? $this->config->main->character,
					dt: $quote->time??time(),
					msg: $quote->quote,
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All quotes imported');
	}

	/** @param list<Schema\RaffleBonus> $bonuses */
	public function importRaffleBonus(array $bonuses): void {
		$this->logger->notice('Importing {num_bonuses} raffle bonuses', [
			'num_bonuses' => count($bonuses),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all raffle bonuses');
			$this->db->table(RaffleBonus::getTable())->truncate();
			foreach ($bonuses as $bonus) {
				$name = $this->characterToName($bonus->character??null);
				if (!isset($name)) {
					continue;
				}
				$this->db->insert(new RaffleBonus(
					name: $name,
					bonus: (int)floor($bonus->raffleBonus),
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All raffle bonuses imported');
	}

	/** @param list<Schema\RaidBlock> $blocks */
	public function importRaidBlocks(array $blocks): void {
		$this->logger->notice('Importing {num_blocks} raid blocks', [
			'num_blocks' => count($blocks),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all raid blocks');
			$this->db->table(RaidBlock::getTable())->truncate();
			foreach ($blocks as $block) {
				$name = $this->characterToName($block->character);
				if (!isset($name)) {
					continue;
				}
				$this->db->insert(new RaidBlock(
					player: $name,
					blocked_from: $block->blockedFrom->value,
					blocked_by: ($this->characterToName($block->blockedBy)) ?? $this->config->main->character,
					reason: $block->blockedReason ?? 'No reason given',
					time: $block->blockStart ?? time(),
					expiration: $block->blockEnd,
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All raid blocks imported');
	}

	/** @param list<Schema\Raid> $raids */
	public function importRaids(array $raids): void {
		$this->logger->notice('Importing {num_raids} raids', [
			'num_raids' => count($raids),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all raids');
			$this->db->table(Raid::getTable())->truncate();
			$this->db->table(RaidLog::getTable())->truncate();
			$this->db->table(RaidMember::getTable())->truncate();
			foreach ($raids as $raid) {
				$history = $raid->history ?? [];
				usort(
					$history,
					static function (Schema\RaidState $o1, Schema\RaidState $o2): int {
						return $o1->time <=> $o2->time;
					}
				);
				$lastEntry = null;
				if (count($history) > 0) {
					$lastEntry = $history[count($history)-1] ?? null;
				}
				$entry = new Raid(
					started: $raid->time ?? time(),
					started_by: $this->config->main->character,
					stopped: isset($lastEntry) ? $lastEntry->time : $raid->time ?? time(),
					stopped_by: $this->config->main->character,
					description: $raid->raidDescription ?? 'No description',
					seconds_per_point: $raid->raidSecondsPerPoint ?? 0,
					announce_interval: $raid->raidAnnounceInterval ?? $this->settingManager->getInt('raid_announcement_interval') ?? 0,
					locked: $raid->raidLocked ?? false,
				);
				$raidId = $this->db->insert($entry);
				$historyEntry = new RaidLog(
					description: $entry->description,
					seconds_per_point: $entry->seconds_per_point,
					announce_interval: $entry->announce_interval,
					locked: $entry->locked,
					raid_id: $raidId,
					time: time(),
				);
				foreach ($raid->raiders??[] as $raider) {
					$name = $this->characterToName($raider->character);
					if (!isset($name)) {
						continue;
					}
					$raiderEntry = new RaidMember(
						raid_id: $raidId,
						player: $name,
						joined: $raider->joinTime ?? time(),
						left: $raider->leaveTime ?? time(),
					);
					$this->db->insert($raiderEntry);
				}
				foreach ($history as $state) {
					$historyEntry->time = $state->time ?? time();
					if (isset($state->raidDescription)) {
						$historyEntry->description = $state->raidDescription;
					}
					if (isset($state->raidLocked)) {
						$historyEntry->locked = $state->raidLocked;
					}
					if (isset($state->raidAnnounceInterval)) {
						$historyEntry->announce_interval = $state->raidAnnounceInterval;
					}
					if (isset($state->raidSecondsPerPoint)) {
						$historyEntry->seconds_per_point = $state->raidSecondsPerPoint;
					}
					$this->db->insert($historyEntry);
				}
				if (!count($history)) {
					$this->db->insert($historyEntry);
				}
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All raids imported');
	}

	/** @param list<Schema\RaidPointEntry> $points */
	public function importRaidPoints(array $points): void {
		$this->logger->notice('Importing {num_points} raid points', [
			'num_points' => count($points),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all raid points');
			$this->db->table(RaidPoints::getTable())->truncate();
			foreach ($points as $point) {
				$name = $this->characterToName($point->character??null);
				if (!isset($name)) {
					continue;
				}
				$this->db->insert(new RaidPoints(
					username: $name,
					points: (int)floor($point->raidPoints),
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All raid points imported');
	}

	/** @param list<Schema\RaidPointLog> $points */
	public function importRaidPointsLog(array $points): void {
		$this->logger->notice('Importing {num_point_logs} raid point logs', [
			'num_point_logs' => count($points),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all raid point logs');
			$this->db->table(RaidPointsLog::getTable())->truncate();
			foreach ($points as $point) {
				$name = $this->characterToName($point->character??null);
				if (!isset($name) || (int)floor($point->raidPoints) === 0) {
					continue;
				}
				$this->db->insert(new RaidPointsLog(
					username: $name,
					delta: (int)floor($point->raidPoints),
					time: $point->time ?? time(),
					changed_by: ($this->characterToName($point->givenBy)) ?? $this->config->main->character,
					individual: $point->givenIndividually ?? true,
					raid_id: $point->raidId ?? null,
					reason: $point->reason ?? 'Raid participation',
					ticker: $point->givenByTick ?? false,
				));
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All raid point logs imported');
	}

	/** @param list<Schema\Timer> $timers */
	public function importTimers(array $timers): void {
		$this->logger->notice('Importing {num_timers} timers', [
			'num_timers' => count($timers),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all timers');
			$this->db->table(Timer::getTable())->truncate();
			$timerNum = 1;
			foreach ($timers as $timer) {
				$owner = $this->characterToName($timer->createdBy??null);
				$data = isset($timer->repeatInterval)
					? (string)$timer->repeatInterval
					: null;
				$entry = new Timer(
					name: $timer->timerName ?? ($this->characterToName($timer->createdBy??null)) ?? $this->config->main->character . "-{$timerNum}",
					owner: $owner ?? $this->config->main->character,
					data: $data,
					mode: $this->channelsToMode($timer->channels??[]),
					endtime: $timer->endTime,
					callback: isset($data) ? 'timercontroller.repeatingTimerCallback' : 'timercontroller.timerCallback',
					alerts: [],
					settime: $timer->startTime ?? time(),
				);
				foreach ($timer->alerts??[] as $alert) {
					$entry->alerts []= new Alert(
						message: $alert->message ?? "Timer <highlight>{$entry->name}<end> has gone off.",
						time: $alert->time,
					);
				}
				if (!count($entry->alerts)) {
					$entry->alerts []= new Alert(
						message: "Timer <highlight>{$entry->name}<end> has gone off.",
						time: $timer->endTime,
					);
				}
				$this->db->insert($entry);
				$timerNum++;
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All timers imported');
	}

	/** @param list<TrackedUser> $trackedUsers */
	public function importTrackedCharacters(array $trackedUsers): void {
		$this->logger->notice('Importing {num_tracked_users} tracked users', [
			'num_tracked_users' => count($trackedUsers),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all tracked users');
			$this->db->table(TrackedUser::getTable())->truncate();
			$this->db->table(Tracking::getTable())->truncate();
			foreach ($trackedUsers as $trackedUser) {
				$name = $this->characterToName($trackedUser->character??null);
				if (!isset($name)) {
					continue;
				}
				$id = $trackedUser->character->id ?? $this->chatBot->getUid($name);
				if ($id === null) {
					continue;
				}
				$this->db->insert(new TrackedUser(
					uid: $id,
					name: $name,
					added_by: ($this->characterToName($trackedUser->addedBy??null)) ?? $this->config->main->character,
					added_dt: $trackedUser->addedTime ?? time(),
				));
				foreach ($trackedUser->events??[] as $event) {
					$this->db->insert(new Tracking(
						uid: $id,
						dt: $event->time,
						event: $event->event,
					));
				}
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All raid blocks imported');
	}

	/**
	 * @param list<Schema\CommentCategory> $categories
	 * @param array<string,string>         $rankMap
	 */
	public function importCommentCategories(array $categories, array $rankMap): void {
		$this->logger->notice('Importing {num_categories} comment categories', [
			'num_categories' => count($categories),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all user-managed comment categories');
			$this->db->table(CommentCategory::getTable())
				->where('user_managed', true)
				->delete();
			foreach ($categories as $category) {
				$oldEntry = $this->commentController->getCategory($category->name);
				$createdBy = $this->characterToName($category->createdBy ??null);
				$entry = new CommentCategory(
					name: $category->name,
					created_by: $createdBy ?? $this->config->main->character,
					created_at: $category->createdAt ?? time(),
					min_al_read: $this->getMappedRank($rankMap, $category->minRankToRead ?? 'mod') ?? 'mod',
					min_al_write: $this->getMappedRank($rankMap, $category->minRankToWrite ?? 'admin') ?? 'admin',
				);

				$entry->user_managed = isset($oldEntry) ? $oldEntry->user_managed : !($category->systemEntry ?? false);
				if (isset($oldEntry)) {
					$this->db->update($entry);
				} else {
					$this->db->insert($entry);
				}
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All comment categories imported');
	}

	/** @param list<Schema\Comment> $comments */
	public function importComments(array $comments): void {
		$this->logger->notice('Importing {num_comments} comment(s)', [
			'num_comments' => count($comments),
		]);
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all comments');
			$this->db->table(Comment::getTable())->truncate();
			foreach ($comments as $comment) {
				$name = $this->characterToName($comment->targetCharacter);
				if (!isset($name)) {
					continue;
				}
				$createdBy = $this->characterToName($comment->createdBy ??null);
				$entry = new Comment(
					comment: $comment->comment,
					character: $name,
					created_by: $createdBy ?? $this->config->main->character,
					created_at: $comment->createdAt ?? time(),
					category: $comment->category ?? 'admin',
				);
				if ($this->commentController->getCategory($entry->category) === null) {
					$cat = new CommentCategory(
						name: $entry->category,
						created_by: $this->config->main->character,
						created_at: time(),
						min_al_read: 'mod',
						min_al_write: 'admin',
						user_managed: true,
					);
					$this->db->insert($cat);
				}
				$this->db->insert($entry);
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('All comments imported');
	}

	/** @return array<string,Closure> */
	protected function getImportMapping(): array {
		return [
			'members'           => $this->importMembers(...),
			'alts'              => $this->importAlts(...),
			'auctions'          => $this->importAuctions(...),
			'banlist'           => $this->importBanlist(...),
			'cityCloak'         => $this->importCloak(...),
			'commentCategories' => $this->importCommentCategories(...),
			'comments'          => $this->importComments(...),
			'events'            => $this->importEvents(...),
			'links'             => $this->importLinks(...),
			'news'              => $this->importNews(...),
			'notes'             => $this->importNotes(...),
			'orgNotes'          => $this->importOrgNotes(...),
			'polls'             => $this->importPolls(...),
			'quotes'            => $this->importQuotes(...),
			'raffleBonus'       => $this->importRaffleBonus(...),
			'raidBlocks'        => $this->importRaidBlocks(...),
			'raids'             => $this->importRaids(...),
			'raidPoints'        => $this->importRaidPoints(...),
			'raidPointsLog'     => $this->importRaidPointsLog(...),
			'timers'            => $this->importTimers(...),
			'trackedCharacters' => $this->importTrackedCharacters(...),
		];
	}

	/**
	 * @param iterable<string> $mappings
	 *
	 * @return array<string,string>
	 */
	protected function parseRankMapping(iterable $mappings): array {
		$mapping = [];
		foreach ($mappings as $part) {
			[$key, $value] = explode('=', $part);
			$mapping[$key] = $value;
		}
		return $mapping;
	}

	/** @return list<string> */
	protected function getRanks(object $import): array {
		$ranks = [];
		foreach ($import->members??[] as $member) {
			$ranks[$member->rank] = true;
		}
		foreach ($import->commentCategories??[] as $category) {
			if (isset($category->minRankToRead)) {
				$ranks[$category->minRankToRead] = true;
			}
			if (isset($category->minRankToWrite)) {
				$ranks[$category->minRankToWrite] = true;
			}
		}
		foreach ($import->polls??[] as $poll) {
			if (isset($poll->minRankToVote)) {
				$ranks[$poll->minRankToVote] = true;
			}
		}
		return array_keys($ranks);
	}

	protected function characterToName(?Schema\Character $char): ?string {
		if (!isset($char) || !isset($char->id)) {
			return null;
		}
		$name = $char->name ?? $this->chatBot->getName($char->id);
		if (!isset($name)) {
			$this->logger->notice('Unable to find a name for UID {user_id}', [
				'user_id' => $char->id,
			]);
		}
		return $name;
	}

	/** @param list<AltMain> $alts */
	protected function importAlts(array $alts): void {
		$this->logger->notice('Importing alts for {num_alts} character(s)', [
			'num_alts' => count($alts),
		]);
		$numImported = 0;
		$this->db->awaitBeginTransaction();
		try {
			$this->logger->notice('Deleting all alts');
			$this->db->table(Alt::getTable())->truncate();
			foreach ($alts as $altData) {
				$mainName = $this->characterToName($altData->main);
				if (!isset($mainName)) {
					continue;
				}
				foreach ($altData->alts as $alt) {
					$numImported += $this->importAlt($mainName, $alt);
				}
			}
		} catch (Throwable $e) {
			$this->rollback($e);
			return;
		}
		$this->db->commit();
		$this->logger->notice('{num_imported} alt(s) imported', [
			'num_imported' => $numImported,
		]);
	}

	protected function importAlt(string $mainName, AltChar $alt): int {
		$altName = $this->characterToName($alt->alt);
		if (!isset($altName)) {
			return 0;
		}
		$this->db->insert(new Alt(
			alt: $altName,
			main: $mainName,
			validated_by_main: $alt->validatedByMain ?? true,
			validated_by_alt: $alt->validatedByAlt ?? true,
			added_via: $this->db->getMyname(),
		));
		return 1;
	}

	/** @param array<string,string> $mapping */
	protected function getMappedRank(array $mapping, string $rank): ?string {
		return $mapping[$rank] ?? null;
	}

	/** @param list<Schema\Channel> $channels */
	protected function channelsToMode(array $channels): string {
		return implode(',', array_map(static fn (Schema\Channel $channel) => $channel->toNadybot(), $channels));
	}

	private function rollback(Throwable $e): void {
		$this->logger->error('{error}. Rolling back changes.', [
			'error' => rtrim($e->getMessage(), '.'),
			'exception' => $e,
		]);
		$this->db->rollback();
	}

	private function loadAndParseExportFile(string $fileName, CmdContext $sendto): ?Schema\Export {
		if (!$this->fs->exists($fileName)) {
			$sendto->reply("No export file <highlight>{$fileName}<end> found.");
			return null;
		}
		$this->logger->notice('Decoding the JSON data');
		try {
			$import = json_decode($this->fs->read($fileName), true);
		} catch (FilesystemException $e) {
			$sendto->reply("Error reading <highlight>{$fileName}<end>: ".
				$e->getMessage() . '.');
			return null;
		} catch (Throwable $e) {
			$sendto->reply("Error decoding <highlight>{$fileName}<end>.");
			return null;
		}
		if (!is_array($import)) {
			$sendto->reply("The file <highlight>{$fileName}<end> is not a valid export file.");
			return null;
		}
		$this->logger->notice('Loading schema data');
		$sendto->reply('Validating the import data. This could take a while.');
		$mapper = new ObjectMapperUsingReflection(
			new DefinitionProvider(
				keyFormatter: new KeyFormatterWithoutConversion(),
			),
		);
		try {
			$data = $mapper->hydrateObject(Schema\Export::class, $import);
		} catch (UnableToHydrateObject $e) {
			$sendto->reply('The import data is not valid: <highlight>' . $e->getMessage() . '<end>.');
			return null;
		}
		return $data;
	}
}
