<?php declare(strict_types=1);

namespace Nadybot\Modules\NANO_MODULE;

use Nadybot\Core\Attributes\DB\MapRead;
use Nadybot\Core\{DBRow};

class Nano extends DBRow {
	/** @param string[] $professions */
	public function __construct(
		public int $nano_id,
		public int $ql,
		public string $nano_name,
		public string $school,
		public string $strain,
		public int $strain_id,
		public string $sub_strain,
		#[MapRead([self::class, 'parseProfessions'])] public array $professions,
		public string $location,
		public int $nano_cost,
		public bool $froob_friendly,
		public int $sort_order,
		public bool $nano_deck,
		public ?int $crystal_id=null,
		public ?string $crystal_name=null,
		public ?int $min_level=null,
		public ?int $spec=null,
		public ?int $mm=null,
		public ?int $bm=null,
		public ?int $pm=null,
		public ?int $si=null,
		public ?int $ts=null,
		public ?int $mc=null,
	) {
	}

	/** @return string[] */
	public static function parseProfessions(string $professions): array {
		return explode(':', $professions);
	}
}
