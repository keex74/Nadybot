<?php declare(strict_types=1);

namespace Nadybot\Modules\IMPLANT_MODULE\Migrations\Designer;

use Illuminate\Database\Schema\Blueprint;
use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\{DB, SchemaMigration};
use Nadybot\Modules\IMPLANT_MODULE\ClusterImplantMap;
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 2021_04_26_14_19_12, shared: true)]
class CreateClusterImplantMapTable implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$table = ClusterImplantMap::getTable();
		$db->schema()->dropIfExists($table);
		$db->schema()->create($table, static function (Blueprint $table): void {
			$table->integer('ImplantTypeID');
			$table->integer('ClusterID');
			$table->integer('ClusterTypeID');
		});
	}
}
