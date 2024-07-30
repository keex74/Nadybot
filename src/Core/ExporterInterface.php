<?php declare(strict_types=1);

namespace Nadybot\Core;

use Psr\Log\LoggerInterface;

interface ExporterInterface {
	/** @return list<object> */
	public function export(DB $db, LoggerInterface $logger): array;
}
