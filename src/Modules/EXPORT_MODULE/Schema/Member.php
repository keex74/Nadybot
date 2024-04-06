<?php declare(strict_types=1);

namespace Nadybot\Modules\EXPORT_MODULE\Schema;

class Member {
	/**
	 * @param Character $character           The character
	 * @param string    $rank                What is the rank/access level of this character on the bot?
	 * @param ?bool     $autoInvite          Does this character automatically get invited to this bot's private channel?
	 * @param ?string   $logonMessage        Extra-message to display when the character logs on
	 * @param ?string   $logoffMessage       Extra-message to display when the character logs off
	 * @param ?bool     $receiveMassMessages Does the character want to receive mass messages?
	 * @param ?bool     $receiveMassInvites  Does the character want to receive mass invites?
	 * @param ?int      $joinedTime          The unix timestamp when this character was made a member of the bot
	 */
	public function __construct(
		public Character $character,
		public string $rank,
		public ?bool $autoInvite=null,
		public ?string $logonMessage=null,
		public ?string $logoffMessage=null,
		public ?bool $receiveMassMessages=null,
		public ?bool $receiveMassInvites=null,
		public ?int $joinedTime=null,
	) {
	}
}
