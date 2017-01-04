<?php 
// function ocrCheck($string) {
// 	$allOcr = json_decode(file_get_contents('json/ocr_v2.json'));
// 	foreach($allOcr->regex as $ocr){
// 		// find, replace, useREonlyToFind
// 	}
// 	foreach($allOcr->string as $ocr){
// 		// find, replace, preserveCase, caseSensitive, wholeWord
// 		$pattern = ($ocr->wholeWord) ? '/((?<=\s)|\b|^)'.preg_quote($ocr->find).'($|\b)/i' : '/'.preg_quote($ocr->find).'/i';
// 		if($ocr->preserveCase) {
// 			$matches = array();
// 			if (preg_match($pattern, $string, $matches)) {
// 				$ocr->replace = (startsWithUpper($matches[0])) ? ucfirst($ocr->replace) : lcfirst($ocr->replace) ;
// 			}
// 		}
// 		$string = preg_replace($pattern, $ocr->replace, $string);
// 	}
// 	return $string;
// }

// function startsWithUpper($str) {
// 	echo $str;
// 	$alphanumericChr = false;
// 	for($i = 0; $i < strlen($str) && !$alphanumericChr; $i++) {
// 		$chr = mb_substr ($str, $i, 1, "windows-1252");	
// 		$alphanumericChr = ctype_alpha($chr);
// 	}
    
//     return mb_strtolower($chr, "windows-1252") != $chr;
// }


function ocrCheck($string) {
	$allOcr = json_decode(file_get_contents('json/ocr_v2.json'));
	$returnArray = array();
	$matches = array();
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
		$pattern = ($ocr->wholeWord) ? '/((?<=\s)|\b|^)'.preg_quote($ocr->find).'($|\b|(?=\s))/i' : '/'.preg_quote($ocr->find).'/i';
        if(preg_match($pattern, $string, $matches)) {
        	//FUNCION PARA MAS DE UN HIGHLIGHT EN ORACIÓN
			$returnArray['found'] = '<span class="highlight">'.$matches[0].'</span>';
			if($ocr->preserveCase) {
                $ocr->replace = (startsWithUpper($matches[0])) ? ucfirst($ocr->replace) : lcfirst($ocr->replace) ;
			}
			//FUNCION PARA MAS DE UN HIGHLIGHT EN ORACIÓN
			$returnArray['replaced'] = '<span class="highlight">'.$ocr->replace.'</span>';
			$returnArray['ocredLine'] = preg_replace($pattern, $ocr->replace, $string);
			$string = $returnArray['ocredLine'];

		}
	}
	return $returnArray;
}

function startsWithUpper($str) {
	$alphaChr = false;
	for($i = 0; $i < strlen($str) && !$alphaChr; $i++) {
		$chr = mb_substr ($str, $i, 1, "windows-1252");	
		$alphaChr = ctype_alpha($chr);
	}
    
    return mb_strtolower($chr, "windows-1252") != $chr;
}




function highlightChange() {

}



?>