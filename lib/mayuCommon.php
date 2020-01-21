<?php
require(__DIR__ . '/../vendor/autoload.php');
use MessagePack\BufferUnpacker;

function pre($arr) {
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

function getGameItems() {
	if( !file_exists(__DIR__ . '/../gameFiles/compiled/items.json') || ( filectime(__DIR__ . '/../gameFiles/raw/compileditems.msgpack') > filectime(__DIR__ . '/../gameFiles/compiled/items.json') ) ) {
		$compileditems = boundless_msgpack(__DIR__ . '/../gameFiles/raw/compileditems.msgpack');
		$items = [];
		foreach($compileditems as $item) {
			$items[$item['name']] = [
				'id' => $item['id'],
				'stringId' => $item['stringID']
			];
		}
		file_put_contents(__DIR__ . '/../gameFiles/compiled/items.json', json_encode($items));
	}
	return json_decode(file_get_contents(__DIR__ . '/../gameFiles/compiled/items.json'), true);
}
function boundless_msgpack($path) {
	$packed = file_get_contents($path);
	$unpacker = new BufferUnpacker($packed);
	$msgData = $unpacker->unpack();
	$result = mapMsgpackKeys($msgData);
	return $result;
}
function mapMsgpackKeys($msgData) {
	$map = [];
	$vals = $msgData[0];
	$keys = $msgData[1];
	foreach($vals as $i => $val) {
		$key = $i;
		if(isset($keys[$i])) {
			$key = $keys[$i];
		}
		else {
			//throw('Msgpack key with id '+i+' not found in the keys array');
		}
		
		if(is_array($val)) {
			$map[$key] = mapMsgpackKeys([$val, $keys]);
		}
		else {
			$map[$key] = $val;
		}
	}
	return $map;
}
?>