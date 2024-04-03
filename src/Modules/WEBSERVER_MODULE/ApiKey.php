<?php declare(strict_types=1);

namespace Nadybot\Modules\WEBSERVER_MODULE;

use DateTimeInterface;
use Nadybot\Core\Attributes\DB\{AutoInc, Table};
use Nadybot\Core\DBRow;
use Safe\DateTimeImmutable;

#[Table(name: 'api_key')]
class ApiKey extends DBRow {
	public DateTimeInterface $created;

	public function __construct(
		public string $character,
		public string $token,
		public string $pubkey,
		#[AutoInc] public ?int $id=null,
		public int $last_sequence_nr=0,
		?DateTimeInterface $created=null,
	) {
		$this->created = $created ?? new DateTimeImmutable();
	}
}
