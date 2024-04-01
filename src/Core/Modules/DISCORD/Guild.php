<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use DateTimeImmutable;
use Stringable;

class Guild implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param string             $id                            guild id
	 * @param string             $name                          guild name (2-100 characters, excluding trailing and leading whitespace)
	 * @param ?string            $icon                          icon hash
	 * @param ?string            $splash                        splash hash
	 * @param ?string            $discovery_splash              discovery splash hash; only present for guilds with the "DISCOVERABLE" feature
	 * @param ?string            $owner_id                      id of owner
	 * @param string             $permissions_new               total permissions for the user in the guild (excludes overrides)
	 * @param ?string            $region                        voice region id for the guild
	 * @param ?string            $afk_channel_id                id of afk channel
	 * @param ?int               $afk_timeout                   afk timeout in seconds
	 * @param int                $verification_level            verification level required for the guild
	 * @param ?int               $default_message_notifications default message notifications level
	 * @param ?int               $explicit_content_filter       explicit content filter level
	 * @param ?int               $mfa_level                     required MFA level for the guild
	 * @param ?string            $application_id                application id of the guild creator if it is bot-created
	 * @param bool               $widget_enabled                true if the server widget is enabled
	 * @param ?string            $widget_channel_id             the channel id that the widget will generate an invite to, or null if set to no invite
	 * @param ?string            $system_channel_id             the id of the channel where guild notices such as welcome messages and boost events are posted
	 * @param ?int               $system_channel_flags          system channel flags
	 * @param ?string            $rules_channel_id              the id of the channel where guilds with the "PUBLIC" feature can display rules and/or guidelines
	 * @param ?DateTimeImmutable $joined_at                     when this guild was joined at
	 * @param ?bool              $large                         true if this is considered a large guild
	 * @param ?bool              $unavailable                   true if this guild is unavailable due to an outage
	 * @param ?int               $member_count                  total number of members in this guild
	 * @param ?mixed[]           $presences                     presences of the members in the guild, will only include non-offline members if the size is greater than large threshold
	 * @param ?int               $max_members                   the maximum number of members for the guild
	 * @param ?string            $vanity_url_code               the vanity url code for the guild
	 * @param ?string            $description                   the description for the guild, if the guild is discoverable
	 * @param ?string            $banner                        banner hash
	 * @param ?int               $premium_tier                  premium tier (Server Boost level)
	 * @param int                $premium_subscription_count    the number of boosts this guild currently has
	 * @param ?string            $preferred_locale              the preferred locale of a guild with the "PUBLIC" feature; used in server discovery and notices from Discord; defaults to "en-US"
	 * @param ?string            $public_updates_channel_id     the id of the channel where admins and moderators of guilds with the "PUBLIC" feature receive notices from Discord
	 * @param ?int               $max_video_channel_users       the maximum amount of users in a video channel
	 * @param int                $approximate_member_count      approximate number of members in this guild, returned from the GET /guild/<id> endpoint when with_counts is true
	 * @param int                $approximate_presence_count    approximate number of non-offline members in this guild, returned from the GET /guild/<id> endpoint when with_counts is true
	 * @param bool               $owner                         true if the user is the owner of the guild
	 * @param int                $permissions                   legacy total permissions for the user in the guild (excludes overrides)
	 * @param Role[]             $roles                         roles in the guild
	 * @param Emoji[]            $emojis                        custom guild emojis
	 * @param string[]           $features                      enabled guild features
	 * @param VoiceState[]       $voice_states                  states of members currently in voice channels; lacks the guild_id key
	 * @param GuildMember[]      $members                       users in the guild
	 * @param DiscordChannel[]   $channels                      channels in the guild
	 * @param ?int               $max_presences                 the maximum number of presences for the guild (the default value, currently 25000, is in effect when null is returned)
	 */
	public function __construct(
		public string $id,
		public string $name,
		public ?string $icon,
		public ?string $splash,
		public ?string $discovery_splash,
		public ?string $owner_id,
		public ?string $permissions_new,
		public ?string $region,
		public ?string $afk_channel_id,
		public ?int $afk_timeout,
		public int $verification_level,
		public ?int $default_message_notifications,
		public ?int $explicit_content_filter,
		public ?int $mfa_level,
		public ?string $application_id,
		public ?bool $widget_enabled,
		public ?string $widget_channel_id,
		public ?string $system_channel_id,
		public ?int $system_channel_flags,
		public ?string $rules_channel_id,
		public ?DateTimeImmutable $joined_at,
		public ?bool $large,
		public ?bool $unavailable,
		public ?int $member_count,
		public ?array $presences,
		public ?int $max_members,
		public ?string $vanity_url_code,
		public ?string $description,
		public ?string $banner,
		public ?int $premium_tier,
		public int $premium_subscription_count,
		public ?string $preferred_locale,
		public ?string $public_updates_channel_id,
		public ?int $max_video_channel_users,
		public ?int $approximate_member_count,
		public ?int $approximate_presence_count,
		public bool $owner=false,
		public int $permissions=0,
		public array $roles=[],
		public array $emojis=[],
		public array $features=[],
		public array $voice_states=[],
		public array $members=[],
		public array $channels=[],
		public ?int $max_presences=25_000,
	) {
	}
}
