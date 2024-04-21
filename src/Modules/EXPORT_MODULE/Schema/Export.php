<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;

class Export {
	/**
	 * @param AltMain[]          $alts
	 * @param Auction[]          $auctions
	 * @param Ban[]              $banlist
	 * @param CommentCategory[]  $commentCategories
	 * @param Comment[]          $comments
	 * @param CloakEntry[]       $cityCloak
	 * @param Event[]            $events
	 * @param Link[]             $links
	 * @param Member[]           $members
	 * @param News[]             $news
	 * @param Note[]             $notes
	 * @param OrgNote[]          $orgNotes
	 * @param Poll[]             $polls
	 * @param Quote[]            $quotes
	 * @param RaffleBonus[]      $raffleBonus
	 * @param RaidBlock[]        $raidBlocks
	 * @param Raid[]             $raids
	 * @param RaidPointEntry[]   $raidPoints
	 * @param RaidPointLog[]     $raidPointsLog
	 * @param Timer[]            $timers
	 * @param TrackedCharacter[] $trackedCharacters
	 *
	 * @psalm-param list<AltMain>          $alts
	 * @psalm-param list<Auction>          $auctions
	 * @psalm-param list<Ban>              $banlist
	 * @psalm-param list<CommentCategory>  $commentCategories
	 * @psalm-param list<Comment>          $comments
	 * @psalm-param list<CloakEntry>       $cityCloak
	 * @psalm-param list<Event>            $events
	 * @psalm-param list<Link>             $links
	 * @psalm-param list<Member>           $members
	 * @psalm-param list<News>             $news
	 * @psalm-param list<Note>             $notes
	 * @psalm-param list<OrgNote>          $orgNotes
	 * @psalm-param list<Poll>             $polls
	 * @psalm-param list<Quote>            $quotes
	 * @psalm-param list<RaffleBonus>      $raffleBonus
	 * @psalm-param list<RaidBlock>        $raidBlocks
	 * @psalm-param list<Raid>             $raids
	 * @psalm-param list<RaidPointEntry>   $raidPoints
	 * @psalm-param list<RaidPointLog>     $raidPointsLog
	 * @psalm-param list<Timer>            $timers
	 * @psalm-param list<TrackedCharacter> $trackedCharacters
	 */
	public function __construct(
		#[CastListToType(AltMain::class)] public array $alts=[],
		#[CastListToType(Auction::class)] public array $auctions=[],
		#[CastListToType(Ban::class)] public array $banlist=[],
		#[CastListToType(CommentCategory::class)] public array $commentCategories=[],
		#[CastListToType(Comment::class)] public array $comments=[],
		#[CastListToType(CloakEntry::class)] public array $cityCloak=[],
		#[CastListToType(Event::class)] public array $events=[],
		#[CastListToType(Link::class)] public array $links=[],
		#[CastListToType(Member::class)] public array $members=[],
		#[CastListToType(News::class)] public array $news=[],
		#[CastListToType(Note::class)] public array $notes=[],
		#[CastListToType(OrgNote::class)] public array $orgNotes=[],
		#[CastListToType(Poll::class)] public array $polls=[],
		#[CastListToType(Quote::class)] public array $quotes=[],
		#[CastListToType(RaffleBonus::class)] public array $raffleBonus=[],
		#[CastListToType(RaidBlock::class)] public array $raidBlocks=[],
		#[CastListToType(Raid::class)] public array $raids=[],
		#[CastListToType(RaidPointEntry::class)] public array $raidPoints=[],
		#[CastListToType(RaidPointLog::class)] public array $raidPointsLog=[],
		#[CastListToType(Timer::class)] public array $timers=[],
		#[CastListToType(TrackedCharacter::class)] public array $trackedCharacters=[],
	) {
	}
}
