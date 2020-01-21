<?php
require('../lib/mysqli.php');
require('../lib/mayuCommon.php');
header('Content-Type: application/json');

$result = [
];
if(true) {
	$itemResult = queryFetch('SELECT * FROM items');
	$items = [];
	foreach($itemResult as $item) {
		$items[$item['name']] = [
			'id' => $item['id'],
			'stringId' => $item['string_id']
		];
	}
	result($items);
}
//echo '<pre>';
echo json_encode($result, JSON_PRETTY_PRINT);
//echo '</pre>';

function error($str) {
	global $result;
	$result['error'] = $str;
}

function result($res) {
	global $result;
	$result['result'] = $res;
}

?>