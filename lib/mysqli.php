<?php
require(__DIR__ . '/../db.conf');

// Create connection
$db = new mysqli($dbConfig['servername'], $dbConfig['username'], $dbConfig['password']);

$driver = new mysqli_driver();
$driver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else {
	$db->set_charset("utf8");
	$db->select_db($dbConfig['databasename']);
}

function queryFetch($sql, $bindTypes = null, $bindData = null) {
	$q = executeQuery($sql, $bindTypes, $bindData);
	$result = $q->get_result();
	$resultArr = Array();
	while($row = $result->fetch_assoc()) {
		array_push($resultArr, $row);
	}
	$q->free_result();
	return $resultArr;
}

function query($sql, $bindTypes = null, $bindData = null) {
	$q = executeQuery($sql, $bindTypes, $bindData);
	//$result = $q->get_result();
	//$q->free_result();
	//return $result;
	return $q;
}

function queryBatch($initialSql, $batchData) {
	$batchSql = [];
	$batchLen = strlen($batchData[0][0]);
	$bindSlots = array_fill(0, $batchLen, '?');
	$qBindTypes = '';
	$qData = [];
	foreach($batchData as $bData) {
		array_push($batchSql, '('.join(', ', $bindSlots).')');
		$qBindTypes .= $bData[0];
		$qData = array_merge($qData, $bData[1]);
	}
	return query($initialSql.join(',', $batchSql), $qBindTypes, $qData);
}

function executeQuery($sql, $bindTypes, $bindData) {
	global $db;
	$q = $db->prepare($sql);
	if($q !== false) {
		if($bindTypes !== null && $bindData !== null && strlen($bindTypes) == sizeof($bindData)) {
			if(is_string($bindData) || is_numeric($bindData)) {
				$q->bind_param($bindTypes, $bindData);
			}
			else if(is_array($bindData)) {
				$q->bind_param($bindTypes, ...$bindData);
			}
		}
		else if($bindTypes !== null && $bindData !== null) {
			throw new Exception("Bind type and data lengths don't match (".strlen($bindTypes)."".count($bindData));
		}
		$q->execute();
		return $q;
	}
}

?>