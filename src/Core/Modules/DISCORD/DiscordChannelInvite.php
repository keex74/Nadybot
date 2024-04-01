<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use DateTimeImmutable;
use Stringable;

class DiscordChannelInvite implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param DiscordChannel     $channel                    The channel this invite is for
	 * @param ?Guild             $guild                      Partial guild object
	 * @param ?DiscordUser       $inviter                    The user who created the invite
	 * @param ?int               $target_type                the type of target for this voice channel invite
	 * @param ?DiscordUser       $target_user                the user whose stream to display for this voice channel stream invite
	 * @param ?int               $approximate_presence_count approximate count of online members, returned from the GET /invites/<code> endpoint when with_counts is true
	 * @param ?int               $approximate_member_count   approximate count of total members, returned from the GET /invites/<code> endpoint when with_counts is true
	 * @param ?DateTimeImmutable $expires_at                 the expiration date of this invite, returned from the GET /invites/<code> endpoint when with_expiration is true
	 */
	public function __construct(
		public string $code,
		public DiscordChannel $channel,
		public ?Guild $guild=null,
		public ?DiscordUser $inviter=null,
		public ?int $target_type=null,
		public ?DiscordUser $target_user=null,
		public ?int $approximate_presence_count=null,
		public ?int $approximate_member_count=null,
		public ?DateTimeImmutable $expires_at=null,
	) {
	}
}
