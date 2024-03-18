<?php declare(strict_types=1);

namespace Nadybot\Modules\MOB_MODULE\Migrations;

use Nadybot\Core\Attributes as NCA;
use Nadybot\Core\DBSchema\{RouteHopColor, RouteHopFormat};
use Nadybot\Core\Routing\Source;
use Nadybot\Core\{DB, MessageHub, SchemaMigration};
use Psr\Log\LoggerInterface;

#[NCA\Migration(order: 20230331211436)]
class SetRouteFormat implements SchemaMigration {
	public function migrate(LoggerInterface $logger, DB $db): void {
		$rhf = new RouteHopFormat();
		$rhf->hop = "mobs";
		$rhf->render = false;
		$rhf->format = 'MOBS';
		$db->insert(Source::DB_TABLE, $rhf);

		$rhc = new RouteHopColor();
		$rhc->hop = 'mobs';
		$rhc->tag_color = '00A9B5';
		$db->insert(MessageHub::DB_TABLE_COLORS, $rhc);
	}
}
