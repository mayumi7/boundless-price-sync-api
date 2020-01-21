<?php
require('../lib/mysqli.php');
require('../lib/mayuCommon.php');
header('Content-Type: application/json');

$result = [
];
if(isset($_GET['beacon'])) {
	$beaconName = $_GET['beacon'];
	$orders = queryFetch("SELECT * FROM orders WHERE beacon = ?", "s", $beaconName);
	$res = [
		'buy' => [],
		'sell' => [],
		'info' => [
			'planets' => [],
			'itemId' => null,
			'itemName' => $beaconName
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