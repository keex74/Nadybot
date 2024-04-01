<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE\Model;

use Nadybot\Core\Modules\DISCORD\{DiscordEmbed, ReducedStringableTrait};
use Stringable;

class InteractionCallbackData implements Stringable {
	use ReducedStringableTrait;

	/** do not include any embeds when serializing this message */
	public const SUPPRESS_EMBEDS = 4;

	/** this message is only visible to the user who invoked the Interaction */
	public const EPHEMERAL = 64;

	/**
	 * @param ?bool           $tts              is the response TTS
	 * @param ?string         $content          message content
	 * @param ?DiscordEmbed[] $embeds           supports up to 10 embeds
	 * @param ?object         $allowed_mentions allowed mentions object
	 * @param ?int            $flags            message flags combined as a bitfield
	 *                                          (only SUPPRESS_EMBEDS and EPHEMERAL can be set)
	 * @param ?object[]       $components       message components
	 * @param ?object[]       $attachments      attachment objects with filename and description
	 */
	public function __construct(
		public ?bool $tts=null,
		public ?string $content=null,
		public ?array $embeds=null,
		public ?object $allowed_mentions=null,
		public ?int $flags=null,
		public ?array $components=null,
		public ?array $attachments=null,
	) {
	}
}
