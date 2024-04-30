<?php declare(strict_types=1);

$data = file_get_contents('src/Modules/ITEMS_MODULE/item_types.json');
$json = json_decode($data, true);
$types = [];
foreach ($json as $chunk) {
	$types[$chunk['aoid']] = $chunk['type'];
}

$data = file('src/Modules/ITEMS_MODULE/aodb.csv');
$result = [];
foreach ($data as $line) {
	$line = trim($line);
	if (str_starts_with($line, '#')) {
	} elseif (str_starts_with($line, 'lowid')) {
		$line .= ',type';
	} else {
		$parts = explode(',', $line);
		$aoid = (int)$parts[0];
		$line .= ',' . ($types[$aoid]??'');
	}
	$result []= $line;
}

file_put_contents('aodb.csv', implode("\n", $result));
