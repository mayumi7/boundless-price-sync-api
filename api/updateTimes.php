<?php
require('../lib/mysqli.php');
require('../lib/mayuCommon.php');
header('Content-Type: application/json');

$result = [
];
if(isset($_GET['item'])) {
	$itemName = $_GET['item'];
	$items = getGameItems();
	if(isset($items[$itemName])) {
		$start = microtime();
		$itemId = $items[$itemName]['id'];
		$updateTimes = queryFetch("SELECT * FROM update_times WHERE item_id = ?", "i", $itemId);
		$res = [
			'planets' => $updateTimes
		];
		echo round((microtime() - $start) * 1000);
		result($res);
	}
	else {
		error("Item not found");
	}
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