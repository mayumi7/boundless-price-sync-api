<?php
require('../lib/mysqli.php');
require('../lib/mayuCommon.php');
header('Content-Type: application/json');

$result = [
];
if(true) {
	$planetResult = queryFetch('SELECT * FROM planets');
	$planets = [];
	foreach($planetResult as $planet) {
		$planets[$planet['id']] = $planet['name'];
	}
	result($planets);
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