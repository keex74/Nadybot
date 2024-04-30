<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE;

use function Safe\{json_decode, json_encode};

use Amp\File\{FilesystemException};
use EventSauce\ObjectHydrator\{DefinitionProvider, KeyFormatterWithoutConversion, ObjectMapperUsingReflection, UnableToSerializeObject};
use Nadybot\Core\{
	AccessManager,
	Attributes as NCA,
	CmdContext,
	Config\BotConfig,
	DB,
	DBSchema\Admin,
	DBSchema\Alt,
	DBSchema\BanEntry,
	DBSchema\Member,
	Filesystem,
	ModuleInstance,
	Modules\PREFERENCES\Preferences,
	Nadybot,
};
use Nadybot\Modules\EVENTS_MODULE\EventModel;
use Nadybot\Modules\NOTES_MODULE\{OrgNote};
use Nadybot\Modules\{
	CITY_MODULE\OrgCity,
	COMMENT_MODULE\Comment,
	COMMENT_MODULE\CommentCategory,
	GUILD_MODULE\OrgMember,
	MASSMSG_MODULE\MassMsgController,
	NEWS_MODULE\News,
	NEWS_MODULE\NewsConfirmed,
	NOTES_MODULE\Link,
	NOTES_MODULE\Note,
	QUOTE_MODULE\Quote,
	RAFFLE_MODULE\RaffleBonus,
	RAID_MODULE\DBAuction,
	RAID_MODULE\RaidBlock,
	RAID_MODULE\RaidLog,
	RAID_MODULE\RaidMember,
	RAID_MODULE\RaidPoints,
	RAID_MODULE\RaidPointsLog,
	RAID_MODULE\RaidRank,
	TIMERS_MODULE\TimerController,
	TRACKER_MODULE\TrackedUser,
	TRACKER_MODULE\Tracking,
	VOTE_MODULE\Poll,
	VOTE_MODULE\Vote,
};
use Safe\Exceptions\JsonException;

/**
 * @author Nadyita (RK5) <nadyita@hodorraid.org>
 */
#[
	NCA\Instance,
	NCA\DefineCommand(
		command: 'export',
		accessLevel: 'superadmin',
		description: 'Export the bot configuration and data',
	)
]
class ExportController extends ModuleInstance {
	#[NCA\Inject]
	private Nadybot $chatBot;

	#[NCA\Inject]
	private DB $db;

	#[NCA\Inject]
	private Filesystem $fs;

	#[NCA\Inject]
	private TimerController $timerController;

	#[NCA\Inject]
	private AccessManager $accessManager;

	#[NCA\Inject]
	private Preferences $preferences;

	#[NCA\Inject]
	private BotConfig $config;

	/** Export all of this bot's data into a portable JSON-file */
	#[NCA\HandlesCommand('export')]
	#[NCA\Help\Example(
		command: '<symbol>export 2021-01-31',
		description: "Export everything into 'data/export/2021-01-31.json'"
	)]
	#[NCA\Help\Prologue(
		"The purpose of this command is to create a portable file containing all the\n".
		"data, not settings, of your bot so it can later be imported into another\n".
		'bot.'
	)]
	public function exportCommand(CmdContext $context, string $file): void {
		$dataPath = $this->config->paths->data;
		$fileName = "{$dataPath}/export/" . basename($file);
		if ((pathinfo($fileName)['extension'] ?? '') !== 'json') {
			$fileName .= '.json';
		}
		if (!$this->fs->exists("{$dataPath}/export")) {
			$this->fs->createDirectory("{$dataPath}/export", 0700);
		}
		$context->reply('Starting export...');
		$exports = new Schema\Export(
			alts: $this->exportAlts(),
			auctions: $this->exportAuctions(),
			banlist: $this->exportBanlist(),
			cityCloak: $this->exportCloak(),
			commentCategories: $this->exportCommentCategories(),
			comments: $this->exportComments(),
			events: $this->exportEvents(),
			links: $this->exportLinks(),
			members: $this->exportMembers(),
			news: $this->exportNews(),
			notes: $this->exportNotes(),
			orgNotes: $this->exportOrgNotes(),
			polls: $this->exportPolls(),
			quotes: $this->exportQuotes(),
			raffleBonus: $this->exportRaffleBonus(),
			raidBlocks: $this->exportRaidBlocks(),
			raids: $this->exportRaidLogs(),
			raidPoints: $this->exportRaidPoints(),
			raidPointsLog: $this->exportRaidPointsLog(),
			timers: $this->exportTimers(),
			trackedCharacters: $this->exportTrackedCharacters(),
		);
		$mapper = new ObjectMapperUsingReflection(
			new DefinitionProvider(
				keyFormatter: new KeyFormatterWithoutConversion(),
			),
		);
		try {
				$serialized = $mapper->serializeObject($exports);
				$cleaned = self::stripNull($serialized);
				$output = json_encode($cleaned, \JSON_PRETTY_PRINT|\JSON_UNESCAPED_SLASHES);
		} catch (JsonException | UnableToSerializeObject $e) {
			$context->reply('There was an error exporting the data: ' . $e->getMessage());
			return;
		}
		try {
			$this->fs->write($fileName, $output);
		} catch (FilesystemException $e) {
			$context->reply($e->getMessage());
			return;
		}
		$context->reply("The export was successfully saved in {$fileName}.");
	}

	protected function toChar(?string $name, ?int $uid=null): Schema\Character {
		$id = $uid;
		if (!isset($id) && isset($name)) {
			$id = $this->chatBot->getUid($name);
		}
		return new Schema\Character(
			name: $name,
			id: is_int($id) ? $id : null,
		);
	}

	/** @return list<Schema\AltMain> */
	protected function exportAlts(): array {
		$alts = $this->db->table(Alt::getTable())->asObj(Alt::class);

		/** @var array<string,list<Schema\AltChar>> */
		$data = [];
		foreach ($alts as $alt) {
			if ($alt->main === $alt->alt) {
				continue;
			}
			$data[$alt->main] ??= [];
			$data[$alt->main] []= new Schema\AltChar(
				alt: $this->toChar($alt->alt),
				validatedByMain: $alt->validated_by_main ?? true,
				validatedByAlt: $alt->validated_by_alt ?? true,
			);
		}

		/** @var list<Schema\AltMain> */
		$result = [];
		foreach ($data as $main => $altInfo) {
			$result []= new Schema\AltMain(
				main: $this->toChar($main),
				alts: $altInfo,
			);
		}

		return $result;
	}

	/** @return list<Schema\Member> */
	protected function exportMembers(): array {
		$exported = [];

		/** @var list<Schema\Member> */
		$result = [];

		$members = $this->db->table(Member::getTable())
			->asObj(Member::class);
		foreach ($members as $member) {
			$result []= new Schema\Member(
				rank: 'member',
				character: $this->toChar($member->name),
				autoInvite: (bool)$member->autoinv,
				joinedTime: $member->joined,
			);
			$exported[$member->name] = true;
		}

		$members = $this->db->table(RaidRank::getTable())
			->asObj(RaidRank::class);
		foreach ($members as $member) {
			if (isset($exported[$member->name])) {
				continue;
			}
			$result []= new Schema\Member(
				character: $this->toChar($member->name),
				rank: 'member',
			);
			$exported[$member->name] = true;
		}
		$members = $this->db->table(OrgMember::getTable())
			->where('mode', '!=', 'del')
			->asObj(OrgMember::class);
		foreach ($members as $member) {
			if (isset($exported[$member->name])) {
				continue;
			}
			$result []= new Schema\Member(
				rank: 'member',
				character: $this->toChar($member->name),
				autoInvite: false,
			);
			$exported[$member->name] = true;
		}

		$members = $this->db->table(Admin::getTable())
			->asObj(Admin::class);
		foreach ($members as $member) {
			if (isset($exported[$member->name])) {
				continue;
			}
			$result []= new Schema\Member(
				rank: 'member',
				character: $this->toChar($member->name),
				autoInvite: false,
			);
			$exported[$member->name] = true;
		}
		foreach ($this->config->general->superAdmins as $superAdmin) {
			if (!isset($exported[$superAdmin])) {
				$result []= new Schema\Member(
					character: $this->toChar($superAdmin),
					autoInvite: false,
					rank: 'superadmin',
				);
			}
		}
		foreach ($result as &$datum) {
			assert(isset($datum->character->name));
			$datum->rank = $this->accessManager->getSingleAccessLevel($datum->character->name);
			$logonMessage = $this->preferences->get($datum->character->name, 'logon_msg');
			$logoffMessage = $this->preferences->get($datum->character->name, 'logoff_msg');
			$massMessages = $this->preferences->get($datum->character->name, MassMsgController::PREF_MSGS);
			$massInvites = $this->preferences->get($datum->character->name, MassMsgController::PREF_INVITES);
			if (isset($logonMessage) && strlen($logonMessage)) {
				$datum->logonMessage ??= $logonMessage;
			}
			if (isset($logoffMessage) && strlen($logoffMessage)) {
				$datum->logoffMessage ??= $logoffMessage;
			}
			if (isset($massMessages) && strlen($massMessages)) {
				$datum->receiveMassMessages ??= $massMessages === 'on';
			}
			if (isset($massInvites) && strlen($massInvites)) {
				$datum->receiveMassInvites ??= $massInvites === 'on';
			}
		}
		return $result;
	}

	/** @return list<Schema\Quote> */
	protected function exportQuotes(): array {
		return $this->db->table(Quote::getTable())
			->orderBy('id')
			->asObj(Quote::class)
			->map(function (Quote $quote): Schema\Quote {
				return new Schema\Quote(
					quote: $quote->msg,
					time: $quote->dt,
					contributor: $this->toChar($quote->poster),
				);
			})->toList();
	}

	/** @return list<Schema\Ban> */
	protected function exportBanlist(): array {
		return $this->db->table(BanEntry::getTable())
			->asObj(BanEntry::class)
			->map(function (BanEntry $banEntry): Schema\Ban {
				$name = $this->chatBot->getName($banEntry->charid);
				$ban = new Schema\Ban(
					character: $this->toChar($name, $banEntry->charid),
					bannedBy: $this->toChar($banEntry->admin),
					banReason: $banEntry->reason,
					banStart: $banEntry->time,
				);
				if (isset($banEntry->banend) && $banEntry->banend > 0) {
					$ban->banEnd = $banEntry->banend;
				}
				return $ban;
			})->toList();
	}

	/** @return list<Schema\CloakEntry> */
	protected function exportCloak(): array {
		return $this->db->table(OrgCity::getTable())
			->asObj(OrgCity::class)
			->map(function (OrgCity $cloakEntry): Schema\CloakEntry {
				return new Schema\CloakEntry(
					character: $this->toChar(rtrim($cloakEntry->player, '*')),
					manualEntry: str_ends_with($cloakEntry->player, '*'),
					cloakOn: ($cloakEntry->action === 'on'),
					time: $cloakEntry->time,
				);
			})->toList();
	}

	/** @return list<Schema\Poll> */
	protected function exportPolls(): array {
		return $this->db->table(Poll::getTable())
			->asObj(Poll::class)
			->map(function (Poll $poll): Schema\Poll {
				$export = new Schema\Poll(
					author: $this->toChar($poll->author),
					question: $poll->question,
					answers: [],
					startTime: $poll->started,
					endTime: $poll->started + $poll->duration,
				);
				$answers = [];
				foreach (json_decode($poll->possible_answers, false) as $answer) {
					$answers[$answer] ??= new Schema\Answer(
						answer: $answer,
						votes: [],
					);
				}

				$votes = $this->db->table(Vote::getTable())
					->where('poll_id', $poll->id)
					->asObj(Vote::class);
				foreach ($votes as $vote) {
					if (!isset($vote->answer)) {
						continue;
					}
					$answers[$vote->answer] ??= new Schema\Answer(
						answer: $vote->answer,
						votes: [],
					);
					$answers[$vote->answer]->votes []= new Schema\Vote(
						character: $this->toChar($vote->author),
						voteTime: $vote->time,
					);
				}
				$export->answers = array_values($answers);
				return $export;
			})->toList();
	}

	/** @return list<Schema\RaffleBonus> */
	protected function exportRaffleBonus(): array {
		return $this->db->table(RaffleBonus::getTable())
			->orderBy('name')
			->asObj(RaffleBonus::class)
			->map(function (RaffleBonus $bonus): Schema\RaffleBonus {
				return new Schema\RaffleBonus(
					character: $this->toChar($bonus->name),
					raffleBonus: $bonus->bonus,
				);
			})->toList();
	}

	/** @return list<Schema\RaidBlock> */
	protected function exportRaidBlocks(): array {
		return $this->db->table(RaidBlock::getTable())
			->orderBy('player')
			->asObj(RaidBlock::class)
			->map(function (RaidBlock $block): Schema\RaidBlock {
				$entry = new Schema\RaidBlock(
					character: $this->toChar($block->player),
					blockedFrom: Schema\RaidBlockType::from($block->blocked_from),
					blockedBy: $this->toChar($block->blocked_by),
					blockedReason: $block->reason,
					blockStart: $block->time,
				);
				if (isset($block->expiration)) {
					$entry->blockEnd = $block->expiration;
				}
				return $entry;
			})->toList();
	}

	protected function nullIf(int $value, int $nullvalue=0): ?int {
		return ($value === $nullvalue) ? null : $value;
	}

	/** @return list<Schema\Raid> */
	protected function exportRaidLogs(): array {
		$data = $this->db->table(RaidLog::getTable())
			->orderBy('raid_id')
			->asObjArr(RaidLog::class);

		/** @var array<string,Schema\Raid> */
		$raids = [];
		foreach ($data as $raid) {
			$raids[$raid->raid_id] ??= new Schema\Raid(
				raidId: $raid->raid_id,
				time: $raid->time,
				raidDescription: $raid->description,
				raidLocked: $raid->locked,
				raidAnnounceInterval: $raid->announce_interval,
				raiders: [],
				history: [],
			);
			if ($raid->seconds_per_point > 0) {
				$raids[$raid->raid_id]->raidSecondsPerPoint = $raid->seconds_per_point;
			}
			$raids[$raid->raid_id]->history []= new Schema\RaidState(
				time: $raid->time,
				raidDescription: $raid->description,
				raidLocked: $raid->locked,
				raidAnnounceInterval: $raid->announce_interval,
				raidSecondsPerPoint: $this->nullIf($raid->seconds_per_point),
			);
		}

		$data = $this->db->table(RaidMember::getTable())
			->asObjArr(RaidMember::class);
		foreach ($data as $raidMember) {
			$raider = new Schema\Raider(
				character: $this->toChar($raidMember->player),
				joinTime: $raidMember->joined,
			);
			if (isset($raidMember->left)) {
				$raider->leaveTime = $raidMember->left;
			}
			$raids[$raidMember->raid_id]->raiders []= $raider;
		}
		return array_values($raids);
	}

	/** @return list<Schema\RaidPointEntry> */
	protected function exportRaidPoints(): array {
		return $this->db->table(RaidPoints::getTable())
			->orderBy('username')
			->asObj(RaidPoints::class)
			->map(function (RaidPoints $datum): Schema\RaidPointEntry {
				return new Schema\RaidPointEntry(
					character: $this->toChar($datum->username),
					raidPoints: (float)$datum->points,
				);
			})->toList();
	}

	/** @return list<Schema\RaidPointLog> */
	protected function exportRaidPointsLog(): array {
		return $this->db->table(RaidPointsLog::getTable())
			->orderBy('time')
			->orderBy('username')
			->asObj(RaidPointsLog::class)
			->map(function (RaidPointsLog $datum): Schema\RaidPointLog {
				$raidLog = new Schema\RaidPointLog(
					character: $this->toChar($datum->username),
					raidPoints: (float)$datum->delta,
					time: $datum->time,
					givenBy: $this->toChar($datum->changed_by),
					reason: $datum->reason,
					givenByTick: $datum->ticker,
					givenIndividually: $datum->individual,
				);
				if (isset($datum->raid_id)) {
					$raidLog->raidId = $datum->raid_id;
				}
				return $raidLog;
			})->toList();
	}

	/** @return list<Schema\Timer> */
	protected function exportTimers(): array {
		$timers = $this->timerController->getAllTimers();
		$result = [];
		foreach ($timers as $timer) {
			$channels = array_values(
				array_diff(
					explode(
						',',
						str_replace(['guild', 'both', 'msg'], ['org', 'priv,org', 'tell'], $timer->mode??'')
					),
					['']
				)
			);
			$data = new Schema\Timer(
				startTime: $timer->settime,
				timerName: $timer->name,
				endTime: $timer->endtime ?? $timer->settime,
				createdBy: $this->toChar($timer->owner),
				channels: array_map(Schema\Channel::fromNadybot(...), $channels),
				alerts: [],
			);
			if (isset($timer->data) && (int)$timer->data > 0) {
				$data->repeatInterval = (int)$timer->data;
			}
			foreach ($timer->alerts as $alert) {
				$data->alerts []= new Schema\Alert(
					time: $alert->time,
					message: $alert->message,
				);
			}
			$result []= $data;
		}
		return $result;
	}

	/** @return list<Schema\TrackedCharacter> */
	protected function exportTrackedCharacters(): array {
		$users = $this->db->table(TrackedUser::getTable())
			->orderBy('added_dt')
			->asObjArr(TrackedUser::class);
		$result = [];
		foreach ($users as $user) {
			$result[$user->uid] = new Schema\TrackedCharacter(
				character: $this->toChar($user->name, $user->uid),
				addedTime: $user->added_dt,
				addedBy: $this->toChar($user->added_by),
				events: [],
			);
		}

		$events = $this->db->table(Tracking::getTable())
			->orderBy('dt')
			->asObj(Tracking::class);
		foreach ($events as $event) {
			if (!isset($result[$event->uid])) {
				continue;
			}
			$result[$event->uid]->events []= new Schema\TrackerEvent(
				time: $event->dt,
				event: $event->event,
			);
		}
		return array_values($result);
	}

	/** @return list<Schema\Auction> */
	protected function exportAuctions(): array {
		$auctions = $this->db->table(DBAuction::getTable())
			->orderBy('id')
			->asObj(DBAuction::class);

		/** @var list<Schema\Auction> $result */
		$result = [];
		foreach ($auctions as $auction) {
			$auctionObj = new Schema\Auction(
				item: $auction->item,
				startedBy: $this->toChar($auction->auctioneer),
				timeEnd: $auction->end,
				reimbursed: $auction->reimbursed,
			);
			if (isset($auction->winner)) {
				$auctionObj->winner = $this->toChar($auction->winner);
			}
			if (isset($auction->cost)) {
				$auctionObj->cost = (float)$auction->cost;
			}
			$result []= $auctionObj;
		}

		return $result;
	}

	/** @return list<Schema\News> */
	protected function exportNews(): array {
		return $this->db->table(News::getTable())
			->asObj(News::class)
			->map(function (News $topic): Schema\News {
				$data = new Schema\News(
					author: $this->toChar($topic->name),
					uuid: $topic->uuid,
					addedTime: $topic->time,
					news: $topic->news,
					pinned: $topic->sticky,
					deleted: $topic->deleted,
					confirmedBy: [],
				);

				$confirmations = $this->db->table(NewsConfirmed::getTable())
					->where('id', $topic->id)
					->asObj(NewsConfirmed::class);
				foreach ($confirmations as $confirmation) {
					$data->confirmedBy []= new Schema\NewsConfirmation(
						character: $this->toChar($confirmation->player),
						confirmationTime: $confirmation->time,
					);
				}
				return $data;
			})->toList();
	}

	/** @return list<Schema\Note> */
	protected function exportNotes(): array {
		return $this->db->table(Note::getTable())
			->asObj(Note::class)
			->map(function (Note $note): Schema\Note {
				$data = new Schema\Note(
					owner: $this->toChar($note->owner),
					author: $this->toChar($note->added_by),
					creationTime: $note->dt,
					text: $note->note,
				);
				if ($note->reminder === Note::REMIND_ALL) {
					$data->remind = 'all';
				} elseif ($note->reminder === Note::REMIND_SELF) {
					$data->remind = 'author';
				}
				return $data;
			})->toList();
	}

	/** @return list<Schema\OrgNote> */
	protected function exportOrgNotes(): array {
		return $this->db->table(OrgNote::getTable())
			->asObj(OrgNote::class)
			->map(function (OrgNote $note): Schema\OrgNote {
				return new Schema\OrgNote(
					author: $this->toChar($note->added_by),
					creationTime: $note->added_on,
					text: $note->note,
					uuid: $note->uuid,
				);
			})->toList();
	}

	/** @return list<Schema\Event> */
	protected function exportEvents(): array {
		return $this->db->table(EventModel::getTable())
			->asObj(EventModel::class)
			->map(function (EventModel $event): Schema\Event {
				$attendees = array_values(array_diff(explode(',', $event->event_attendees ?? ''), ['']));

				$data = new Schema\Event(
					createdBy: $this->toChar($event->submitter_name),
					creationTime: $event->time_submitted,
					name: $event->event_name,
					startTime: $event->event_date,
					description: $event->event_desc,
					attendees: array_map($this->toChar(...), $attendees),
				);

				return $data;
			})->toList();
	}

	/** @return list<Schema\Link> */
	protected function exportLinks(): array {
		return $this->db->table(Link::getTable())
			->asObj(Link::class)
			->map(function (Link $link): Schema\Link {
				return new Schema\Link(
					createdBy: $this->toChar($link->name),
					creationTime: $link->dt,
					url: $link->website,
					description: $link->comments,
				);
			})->toList();
	}

	/** @return list<Schema\CommentCategory> */
	protected function exportCommentCategories(): array {
		return $this->db->table(CommentCategory::getTable())
			->asObj(CommentCategory::class)
			->map(function (CommentCategory $category): Schema\CommentCategory {
				return new Schema\CommentCategory(
					name: $category->name,
					createdBy: $this->toChar($category->created_by),
					createdAt: $category->created_at,
					minRankToRead: $category->min_al_read,
					minRankToWrite: $category->min_al_write,
					systemEntry: !$category->user_managed,
				);
			})->toList();
	}

	/** @return list<Schema\Comment> */
	protected function exportComments(): array {
		return $this->db->table(Comment::getTable())
			->asObj(Comment::class)
			->map(function (Comment $comment): Schema\Comment {
				return new Schema\Comment(
					comment: $comment->comment,
					targetCharacter: $this->toChar($comment->character),
					createdBy: $this->toChar($comment->created_by),
					createdAt: $comment->created_at,
					category: $comment->category,
				);
			})->toList();
	}

	/**
	 * @param array<string,mixed> $data
	 *
	 * @return array<string,mixed>
	 */
	private static function stripNull(array $data): array {
		foreach ($data as $key => $value) {
			if (is_null($value)) {
				unset($data[$key]);
			} elseif (is_array($value)) {
				$data[$key] = self::stripNull($value);
			}
		}
		return $data;
	}
}
