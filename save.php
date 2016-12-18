<?php 
if(isset($_POST['updatedJson'])) {
	$json = strip_tags($_POST['updatedJson']);
	$jsonString = json_decode($json);
	if(json_last_error() == JSON_ERROR_NONE) {
		$jsonRecoded = json_encode($jsonString,JSON_PRETTY_PRINT);
	 	file_put_contents("json/data.json",$jsonRecoded);
	 	echo $jsonRecoded;
	}
}
?>