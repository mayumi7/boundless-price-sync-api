<?php
require('../lib/mysqli.php');
require('../lib/mayuCommon.php');
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] === "POST") {
	if(isset($_POST['itemIds']) && isset($_POST['data'])) {
		$items = getGameItems();

		$tok = strtok($_POST['data'], "\n");
		$csvRegex = '#([\d\.\s:\/]*),(\w*),"([\w\s\d]*)","(\w*)","(.*)","(.*)",(\d+),(\d+[,\.]\d+),(\d+)#';
		
		$preg = preg_match($csvRegex, $tok, $csvEntry);
		if($preg == 1) {
			echo 'Data received ['.$csvEntry[3].']';
			// get planet id
			$planets = queryFetch('SELECT * FROM planets WHERE name = ?', "s", $csvEntry[3]);
			$planetId = null;
			if(sizeof($planets) > 0) { // planet exists already
				$planetId = $planets[0]['id'];
			}
			if($planetId == null) { // it's a new planet
				query('INSERT INTO planets (name) VALUES (?)', 's', $csvEntry[3]);
				$planetId = $db->insert_id;
			}
			
			$postItemIds = explode(",", $_POST['itemIds']);
			$batchDelete = [];
			$batchInsert = [];
			foreach($postItemIds as $itemId) { // delete this planet/item combination data from the orders table
				array_push($batchDelete, $db->escape_string($itemId));
				array_push($batchInsert, '('.$planetId.', '.$db->escape_string($itemId).', NOW())');
			}
			query('DELETE FROM orders WHERE item_id IN('.join(',', $batchDelete).') AND planet_id = '.$planetId);
			query('INSERT INTO update_times (planet_id, item_id, update_time) VALUES '.join(',', $batchInsert).' ON DUPLICATE KEY UPDATE update_time = NOW()');
			
			$queryBatch = [];
			$insertedItems = [];
			while ($tok !== false && $tok !== "") {
				$preg = preg_match($csvRegex, $tok, $csvEntry);
				if($preg == 1) {
					//echo implode("|", array_slice($csvEntry, 1))."<br>";
					
					// check item id
					if(isset($items[$csvEntry[4]])) {
						$insertedItems[$csvEntry[4]] = false;
						array_push($queryBatch, ['iidissi', [$items[$csvEntry[4]]['id'],($csvEntry[2] == "Selling" ? 0 : 1),floatval(str_replace(',', '.', $csvEntry[8])),intval($csvEntry[7]),$csvEntry[5],$csvEntry[6],$planetId]]);
					}
					else {
						echo "Item data for [".$csvEntry[4]."] not found in game files";
					}
				}
				else {
					// TODO log unmatched entries for error checking
				}
				$tok = strtok("\n");
			}
			queryBatch('INSERT INTO orders (item_id,buy,price,quantity,guild,beacon,planet_id) VALUES ', $queryBatch);
			$dbItems = queryFetch("SELECT * FROM items WHERE name IN ('".join("','", array_keys($insertedItems))."')");
			foreach($dbItems as $dbItem) { $insertedItems[$dbItem['name']] = true; }
			$missingItems = [];
			foreach($insertedItems as $itemName => $exists) {
				if(!$exists) {
					$item = $items[$itemName];
					array_push($missingItems, ['iss', [$item['id'],$itemName,$item['stringId']]]);
				}
			}
			if(count($missingItems) > 0) {
				queryBatch('INSERT INTO items (id,name,string_id) VALUES ', $missingItems);
			}
			//echo "Data processing complete";
		}
		else {
			echo "Error parsing data (".$tok.")";
		}
	}
	else {
		echo "Data not set";
	}
}
else {
	echo "Data not received";
}

?>