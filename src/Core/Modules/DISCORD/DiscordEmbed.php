<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use DateTimeImmutable;
use EventSauce\ObjectHydrator\PropertyCasters\CastListToType;
use Stringable;

class DiscordEmbed implements Stringable {
	use ReducedStringableTrait;

	/** @param ?DiscordEmbedField[] $fields */
	public function __construct(
		public ?string $title=null,
		public ?string $type='rich',
		public ?string $description=null,
		public ?string $url=null,
		public ?DateTimeImmutable $timestamp=null,
		public ?int $color=null,
		public ?DiscordEmbedFooter $footer=null,
		public ?DiscordEmbedImage $image=null,
		public ?DiscordEmbedThumbnail $thumbnail=null,
		public ?DiscordEmbedVideo $video=null,
		public ?DiscordEmbedProvider $provider=null,
		public ?DiscordEmbedAuthor $author=null,
		#[CastListToType(DiscordEmbedField::class)] public ?array $fields=null,
	) {
	}
}
