<?php 
function ocrCheck($string) {
	$ocrArray = json_decode(file_get_contents('json/ocr.json'));
	// print_r($ocrArray[0]->find);
	
	foreach($ocrArray as $ocr){
		// echo "<br>Find: ".$ocr->find." - Replace: ".$ocr->replace."<br>";
		if($ocr->regex) {
			$pattern = "/".$ocr->find."/";
			$string = preg_replace($pattern,$ocr->replace,$string);
		} else {
			$string = str_replace($ocr->find,$ocr->replace,$string);
		}
	}
	return $string;
}

?>