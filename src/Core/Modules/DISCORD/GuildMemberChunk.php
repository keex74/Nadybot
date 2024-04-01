<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use Stringable;

class GuildMemberChunk implements Stringable {
	use ReducedStringableTrait;

	/**
	 * @param string        $guild_id    the id of the guild
	 * @param int           $chunk_index the chunk index in the expected chunks for this response (0 <= chunk_index < chunk_count)
	 * @param int           $chunk_count the total number of expected chunks for this response
	 * @param GuildMember[] $members     set of guild members
	 * @param ?string[]     $not_found   if passing an invalid id to REQUEST_GUILD_MEMBERS, it will be returned here
	 * @param ?object[]     $presences   if passing true to REQUEST_GUILD_MEMBERS, presences of the returned members will be here
	 * @param ?string       $nonce       the nonce used in the RequestGuildMembers-request
	 */
	public function __construct(
		public string $guild_id,
		public int $chunk_index,
		public int $chunk_count,
		public array $members=[],
		public ?array $not_found=null,
		public ?array $presences=null,
		public ?string $nonce=null,
	) {
	}
}
