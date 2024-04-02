<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use DateTimeImmutable;
use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Stringable;

class GuildMember implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param string[]           $roles         array of role object ids
	 * @param DateTimeImmutable  $joined_at     when the user joined the guild
	 * @param bool               $deaf          whether the user is deafened in voice channels
	 * @param bool               $mute          whether the user is muted in voice channels
	 * @param ?string            $nick          this users guild nickname
	 * @param ?DateTimeImmutable $premium_since when the user started boosting the guild
	 */
	public function __construct(
		#[CastListToType('string')] public array $roles,
		public DateTimeImmutable $joined_at,
		public bool $deaf,
		public bool $mute,
		public ?DiscordUser $user=null,
		public ?string $nick=null,
		public ?DateTimeImmutable $premium_since=null,
	) {
	}

	public function getName(): string {
		if (isset($this->nick)) {
			return $this->nick;
		}
		if (isset($this->user)) {
			return $this->user->getName();
		}
		return 'UnknownUser';
	}
}
