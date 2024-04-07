<?php declare(strict_types=1);

namespace Nadybot\Core\Migrations;

use function Safe\preg_match;
use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;

use Nadybot\Core\DBSchema\RouteHopColor;
use Nadybot\Core\{
	DB,
	DBSchema\Setting,
	Routing\Source,
	Safe,
	SchemaMigration,
};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_08_11_12_55_55)]
class CreateRouteHopColorTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = RouteHopColor::getTable();
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->id();
			$table->string('hop', 50)->default('*');
			$table->string('where', 50)->nullable(true);
			$table->string('tag_color', 6)->nullable(true);
			$table->string('text_color', 6)->nullable(true);
			$table->unique(['hop', 'where']);
		});
		if (strlen($db->getMyguild())) {
			$sysColor = $this->getSetting($db, 'default_guild_color');
		} else {
			$sysColor = $this->getSetting($db, 'default_priv_color');
		}
		$matches = [1 => '89D2E8'];
		if (!isset($sysColor) || !count($matches = Safe::pregMatch('/#([0-9a-f]{6})/i', $sysColor->value??''))) {
			$sysColor = '89D2E8';
		} else {
			$sysColor = $matches[1];
		}
		$db->table($table)->insert([
			'hop' => Source::SYSTEM,
			'text_color' => $sysColor,
		]);
		if (!strlen($db->getMyguild())) {
			return;
		}
		$privSysColor = $this->getSetting($db, 'default_priv_color');
		if (!isset($privSysColor)) {
			return;
		}
		if (!preg_match('/#([0-9a-f]{6})/i', $privSysColor->value??'', $matches)) {
			return;
		}
		$privSysColor = $matches[1]??'';
		if ($privSysColor === $sysColor) {
			return;
		}
		$db->table($table)->insert([
			'hop' => Source::SYSTEM,
			'where' => Source::PRIV . '(' . $db->getMyname() . ')',
			'text_color' => $matches[1]??'',
		]);
	}

	protected function getSetting(DB $db, string $name): ?Setting {
		return $db->table(Setting::getTable())
			->where('name', $name)
			->asObj(Setting::class)
			->first();
	}
}
