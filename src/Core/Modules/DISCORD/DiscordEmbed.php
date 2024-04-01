<?php declare(strict_types=1);

namespace Nadybot\Core\Modules\DISCORD;

use DateTimeImmutable;
use Stringable;

class DiscordEmbed implements Stringable {
	use ReducedStringableTrait;

	/** @param null|DiscordEmbedField[] $fields */
	public function __construct(
		public ?string $title=null,
		public ?string $type='rich',
		public ?string $description=null,
		public ?string $url=null,
		public ?DateTimeImmutable $timestamp=null,
		public ?int $color=null,
		public ?object $footer=null,
		public ?object $image=null,
		public ?object $thumbnail=null,
		public ?object $video=null,
		public ?object $provider=null,
		public ?object $author=null,
		public ?array $fields=null,
	) {
	}
}
