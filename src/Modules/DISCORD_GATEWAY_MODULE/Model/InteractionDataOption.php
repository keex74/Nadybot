<?php declare(strict_types=1);

namespace Nadybot\Modules\DISCORD_GATEWAY_MODULE\Model;

use Nadybot\Core\Modules\DISCORD\ReducedStringableTrait;
use Stringable;

class InteractionDataOption implements Stringable {
	use ReducedStringableTrait;

	public const TYPE_SUB_COMMAND = 1;
	public const TYPE_SUB_COMMAND_GROUP = 2;
	public const TYPE_STRING = 3;

	/** Any integer between -2^53 and 2^53 */
	public const TYPE_INTEGER = 4;
	public const TYPE_BOOLEAN = 5;
	public const TYPE_USER = 6;

	/** Includes all channel types + categories */
	public const TYPE_CHANNEL = 7;
	public const TYPE_ROLE = 8;

	/** Includes users and roles */
	public const TYPE_MENTIONABLE = 9;

	/** Any double between -2^53 and 2^53 */
	public const TYPE_NUMBER = 10;

	/** attachment object */
	public const TYPE_ATTACHMENT = 11;

	/**
	 * @param string                $name    Name of the parameter
	 * @param int                   $type    Value of application command option type
	 * @param null|string|int|float $value   Value of the option resulting from user input
	 * @param ?self[]               $options Present if this option is a group or subcommand
	 * @param ?bool                 $focused true if this option is the currently
	 *                                       focused option for autocomplete
	 */
	public function __construct(
		public string $name,
		public int $type,
		public null|string|int|float $value=null,
		public ?array $options=null,
		public ?bool $focused=null,
	) {
	}

	public function getOptionString(): string {
		if (!isset($this->options)) {
			return (string)$this->value;
		}
		$parts = [];
		foreach ($this->options as $option) {
			$parts []= $option->getOptionString();
		}
		return implode(' ', $parts);
	}
}
