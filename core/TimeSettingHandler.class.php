<?php

namespace Budabot\Core;

use Exception;

require_once 'SettingHandler.class.php';

class TimeSettingHandler extends SettingHandler {
	/**
	 * @var \Budabot\Core\Util $util
	 * @Inject
	 */
	public $util;
	
	public function __construct(DBRow $row) {
		parent::__construct($row);
	}

	/**
	 * @return String
	 */
	public function displayValue() {
		return "<highlight>" . $this->util->unixtimeToReadable($this->row->value) . "<end>";
	}
	
	/**
	 * @return String
	 */
	public function getDescription() {
		$msg = "For this setting you must enter a time value. See <a href='chatcmd:///tell <myname> help budatime'>budatime</a> for info on the format of the 'time' parameter.\n\n";
		$msg .= "To change this setting:\n\n";
		$msg .= "<highlight>/tell <myname> settings save {$this->row->name} <i>time</i><end>\n\n";
		return $msg;
	}
	
	/**
	 * @return String of new value or false if $newValue is invalid
	 */
	public function save($newValue) {
		$time = $this->util->parseTime($newValue);
		if ($time > 0) {
			return $time;
		} else {
			throw new Exception("This is not a valid time for this setting.");
		}
	}
}
