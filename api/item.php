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
		$itemId = $items[$itemName]['id'];
		$orders = queryFetch("SELECT * FROM orders WHERE item_id = ?", "i", $itemId);
		$updateTimes = [];
		if(count($orders) > 0) {
			$updateTimes = queryFetch("SELECT * FROM update_times WHERE item_id = ?", "i", $itemId);
		}
		$res = [
			'buy' => [],
			'sell' => [],
			'info' => [
				'planets' => $updateTimes,
				'itemId' => $itemId,
				'itemName' => $itemName
			]
		];
		foreach($orders as $order) {
			$buy = $order['buy'];
			unset($order['buy']);
			unset($order['id']);
			unset($order['item_id']);
			if($buy == 1) { array_push($res['buy'], $order); }
			else if($buy == 0) { array_push($res['sell'], $order); }
		}
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