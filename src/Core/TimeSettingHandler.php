<?php declare(strict_types=1);

namespace Nadybot\Core;

use Exception;
use Nadybot\Core\Attributes as NCA;

/**
 * Class to represent a time setting for NadyBot
 */
#[NCA\SettingHandler('time')]
class TimeSettingHandler extends SettingHandler {
	/** @inheritDoc */
	public function displayValue(string $sender): string {
		return '<highlight>' . Util::unixtimeToReadable((int)$this->row->value) . '<end>';
	}

	/** @inheritDoc */
	public function getDescription(): string {
		$msg = "For this setting you must enter a time value. See <a href='chatcmd:///tell <myname> help budatime'>budatime</a> for info on the format of the 'time' parameter.\n\n";
		$msg .= "To change this setting:\n\n";
		$msg .= "<highlight>/tell <myname> settings save {$this->row->name} <i>time</i><end>\n\n";
		return $msg;
	}

	/**
	 * @inheritDoc
	 *
	 * @throws \Exception when the time is invalid
	 */
	public function save(string $newValue): string {
		if (ctype_digit($newValue)) {
			return $newValue;
		}
		$time = Util::parseTime($newValue);
		if ($time > 0) {
			return (string)$time;
		}
		throw new Exception('This is not a valid time for this setting.');
	}
}
