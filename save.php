<?php 
// Guardar cambios del editor de JSON
if(isset($_POST['updatedJson'])) {
	$json = strip_tags($_POST['updatedJson']);
	$jsonString = json_decode($json);
	
	$categories = array('tv_show', 'codec', 'format', 'quality', 'rip_group', 'other', 'editor', 'translation', 'enhanced');
	$countCategories = count((array)$jsonString);
	$propertiesExists = true;

	for ($i = 0; $propertiesExists == true && $i < 9; $i++) {
		$propertiesExists = (property_exists($jsonString, $categories[$i])) ? true : false;
	}

	if(json_last_error() == JSON_ERROR_NONE && $propertiesExists && $countCategories == 9) {
		$jsonRecoded = json_encode($jsonString,JSON_PRETTY_PRINT);
	 	file_put_contents("json/data.json",$jsonRecoded);
	 	echo $jsonRecoded;
	}
}
?>