<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class DiscordUser implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param string  $username      the user's username, not unique across the platform
	 * @param string  $discriminator the user's 4-digit discord-tag
	 * @param ?string $avatar        the user's avatar hash
	 * @param ?bool   $bot           whether the user belongs to an OAuth2 application
	 * @param ?bool   $system        whether the user is an Official Discord System user
	 *                               (part of the urgent message system)
	 * @param ?bool   $mfa_enabled   whether the user has two factor enabled on their account
	 * @param ?string $locale        the user's chosen language option
	 * @param ?bool   $verified      whether the email on this account has been verified
	 * @param ?string $email         the user's email
	 * @param ?int    $flags         the flags on a user's account
	 * @param ?int    $premium_type  the type of Nitro subscription on a user's account
	 * @param ?int    $public_flags  the public flags on a user's account
	 */
	public function __construct(
		public string $id,
		public string $username,
		public string $discriminator,
		public ?string $avatar=null,
		public ?bool $bot=null,
		public ?bool $system=null,
		public ?bool $mfa_enabled=null,
		public ?string $locale=null,
		public ?bool $verified=null,
		public ?string $email=null,
		public ?int $flags=null,
		public ?int $premium_type=null,
		public ?int $public_flags=null,
	) {
	}
}
