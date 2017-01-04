<?php 
function ocrCheck($string) {
	$allOcr = json_decode(file_get_contents('json/ocr_v2.json'));
	foreach($allOcr->regex as $ocr){
		// print_r($ocr);
		// find, replace, useREonlyToFind

		// echo "<br>Find: ".$ocr->find." - Replace: ".$ocr->replace."<br>";
		
		// $pattern = "/".$ocr->find."/";
		// echo $ocr->replace.'<br>';
		// $string = preg_replace($pattern, $ocr->replace, $string);
	}
	foreach($allOcr->string as $ocr){
		// find, replace, preserveCase, caseSensitive, wholeWord
		$pattern = ($ocr->wholeWord) ? '/((?<=\s)|\b|^)'.preg_quote($ocr->find).'($|\b)/i' : '/'.preg_quote($ocr->find).'/i';
		// echo $pattern.'<br>';
		if($ocr->preserveCase) {
			$matches = array();
			if (preg_match($pattern, $string, $matches)) {
				// print_r($ocr);
				$ocr->replace = (starts_with_upper($matches[0])) ? ucfirst($ocr->replace) : lcfirst($ocr->replace) ;
			}
		}
		$string = preg_replace($pattern, $ocr->replace, $string);
	}
	return $string;
}

function starts_with_upper($str) {
	echo $str;
	$alphanumericChr = false;
	for($i = 0; $i < strlen($str) && !$alphanumericChr; $i++) {
		$chr = mb_substr ($str, $i, 1, "windows-1252");	
		$alphanumericChr = ctype_alpha($chr);
	}
    
    return mb_strtolower($chr, "windows-1252") != $chr;
}

?>