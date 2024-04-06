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
