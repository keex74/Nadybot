<?php declare(strict_types=1);

namespace Nadybot\Modules\TIMERS_MODULE;

use function Safe\json_decode;

use Nadybot\Core\{Attributes\DB, DBTable};

#[DB\Table(name: 'timers')]
class Timer extends DBTable {
	public int $settime;

	/**
	 * @param string  $name     Name of the timer
	 * @param string  $owner    Name of the person who created that timer
	 * @param ?int    $endtime  Timestamp when this timer goes off
	 * @param string  $callback class.method of the function to call on alerts
	 * @param ?string $mode     Comma-separated list where to display the alerts (priv,guild,discord)
	 * @param ?string $data     For repeating timers, this is the repeat interval in seconds
	 * @param ?int    $id       ID of the timer
	 * @param Alert[] $alerts   A list of alerts, each calling $callback
	 * @param ?int    $settime  Timestamp when this timer was set
	 *
	 * @psalm-param list<Alert> $alerts
	 */
	public function __construct(
		public string $name,
		public string $owner,
		public ?int $endtime,
		public string $callback='timercontroller.timerCallback',
		public ?string $mode=null,
		public ?string $data=null,
		#[DB\AutoInc] public ?int $id=null,
		#[DB\MapRead([self::class, 'decodeAlerts'])] #[DB\MapWrite('json_encode')] public array $alerts=[],
		#[DB\Ignore] public ?string $origin=null,
		?int $settime=null,
	) {
		$this->settime = $settime ?? time();
	}

	/** @return list<Alert> */
	public static function decodeAlerts(?string $alerts): array {
		if (!isset($alerts)) {
			return [];
		}
		$alertsData = json_decode($alerts);
		return array_values(array_map(
			static function (\stdClass $alertData): Alert {
				$alert = new Alert(
					message: $alertData->message,
					time: $alertData->time,
				);
				foreach (get_object_vars($alertData) as $key => $value) {
					$alert->{$key} = $value;
				}
				return $alert;
			},
			$alertsData
		));
	}
}
