<?php declare(strict_types=1);

namespace Nadybot\Core\SettingHandlers;

use Nadybot\Core\DBSchema\Setting;
use Nadybot\Core\{Attributes as NCA, Text};

#[NCA\SettingHandler('bool')]
class BoolSettingHandler extends OptionsSettingHandler {
	/** Construct a new handler out of a given database row */
	public function __construct(Setting $row) {
		$row->options = 'true;false';
		$row->intoptions = '1;0';
		$this->row = $row;
	}

	public function getModifyLink(): string {
		if ($this->row->value === '1') {
			return Text::makeChatcmd('disable', "/tell <myname> settings save {$this->row->name} 0");
		}
		return Text::makeChatcmd('enable', "/tell <myname> settings save {$this->row->name} 1");
	}

	/** @inheritDoc */
	public function getDescription(): string {
		$msg = "This option can only be turned on and off.\n\n";
		return $msg;
	}
}