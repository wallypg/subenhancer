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
	$originalString = $string;
	foreach($allOcr->string as $ocr){
		// find, replace, preserveCase, caseSensitive, wholeWord
		$pattern = ($ocr->wholeWord) ? '/((?<=\s)|\b|^)'.preg_quote($ocr->find).'($|\b|(?=\s))/i' : '/'.preg_quote($ocr->find).'/i';
        if(preg_match($pattern, $string, $matches)) {
        	// echo $originalString.'<br>';
			$returnArray['found'] = (isset($returnArray['found'])) ? highlightChange($originalString, $matches[0], $returnArray['found']) : highlightChange($originalString, $matches[0]);

			// if($ocr->preserveCase) {
   //              $ocr->replace = (startsWithUpper($matches[0])) ? ucfirst($ocr->replace) : lcfirst($ocr->replace) ;
			// }

			$returnArray['ocredLine'] = preg_replace($pattern, $ocr->replace, $string);
			$returnArray['replaced'] = (isset($returnArray['replaced'])) ? highlightChange($returnArray['ocredLine'], $ocr->replace, $returnArray['replaced']) : highlightChange($returnArray['ocredLine'], $ocr->replace);
			$string = $returnArray['ocredLine'];

		}
	}
	// die();
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




function highlightChange($line, $finding, $alreadyHighlighted = NULL) {
	$openTag = '<span class="highlight">';
	$closeTag = '</span>';

	$highlightedLine = str_replace($finding, $openTag.$finding.$closeTag, $line);
	if(!is_null($alreadyHighlighted)) {
		$originalOpenTagPosition = mb_strpos ($alreadyHighlighted, $openTag);
		$originalCloseTagPosition = mb_strpos (str_replace($openTag, '', $alreadyHighlighted), $closeTag, $originalOpenTagPosition);
		// echo $alreadyHighlighted;
		// die();
		// echo $alreadyHighlighted."\n";
		// echo str_replace($openTag, '', $alreadyHighlighted)."\n";
		// echo $originalCloseTagPosition."\n";
		$newOpenTagPosition = mb_strpos ($highlightedLine, $openTag);
		$newCloseTagPosition = mb_strpos (str_replace($openTag, '', $highlightedLine), $closeTag, $newOpenTagPosition);
		
		// echo $alreadyHighlighted."\n";
		// echo mb_strpos (str_replace($openTag, '', $alreadyHighlighted), $closeTag, $originalOpenTagPosition)."\n";


		// echo mb_strpos (str_replace($openTag, '', $highlightedLine), $closeTag, $newOpenTagPosition)."\n";

		// die();
		// echo '$newCloseTagPosition '.$newCloseTagPosition."\n";
		// echo '$originalCloseTagPosition '.$originalCloseTagPosition."\n";

		// echo $line;
		// echo $originalOpenTagPosition.'<br>'.$originalCloseTagPosition.'<br>'.$newOpenTagPosition.'<br>'.$newCloseTagPosition;

		// EQUALS??
		// CASE SENSITIVITY
		// MB REPLACE


		echo $line."\n";
		if($newOpenTagPosition > $originalOpenTagPosition && $newOpenTagPosition < $originalCloseTagPosition) {
			// $line = substr_replace($line, $closeTag, $originalCloseTagPosition, 0);
			$line = ($newCloseTagPosition > $originalCloseTagPosition) ? substr_replace($line, $closeTag, $newCloseTagPosition, 0) : substr_replace($line, $closeTag, $originalCloseTagPosition, 0);
			$line = substr_replace($line, $openTag, $originalOpenTagPosition, 0);
		} else if($newOpenTagPosition > $originalCloseTagPosition) {
			$line = substr_replace($line, $closeTag, $newCloseTagPosition, 0);
			$line = substr_replace($line, $openTag, $originalOpenTagPosition, 0);
		} else if($newOpenTagPosition < $originalOpenTagPosition) {

			echo '$newCloseTagPosition: '.$newCloseTagPosition."\n";
			echo '$originalCloseTagPosition: '.$originalCloseTagPosition."\n";
			// echo '$newOpenTagPosition: '.$newOpenTagPosition."\n";
			// echo '$originalOpenTagPosition: '.$originalOpenTagPosition."\n";
			$line = ($newCloseTagPosition > $originalCloseTagPosition) ? mb_substr_replace($line, $closeTag, $newCloseTagPosition, 0) : mb_substr_replace($line, $closeTag, $originalCloseTagPosition, 0);
			// echo '-----';
			// echo $line."\n";
			// echo $closeTag."\n";
			
			// echo $originalCloseTagPosition."\n";
			// echo mb_substr_replace($line, $closeTag, $originalCloseTagPosition, 0);
			if($newCloseTagPosition > $originalCloseTagPosition) {
			// 	$line = mb_substr($line, 0, $newCloseTagPosition) . $closeTag . mb_substr($line, 0);
				
			} else {
				
			// 	$line = mb_substr($line, 0, $originalCloseTagPosition) . $closeTag . mb_substr($line, 0);
			}
			// echo $line."\n";
			$line = substr_replace($line, $openTag, $newOpenTagPosition, 0);
			// die();
		} else {

		}
		$highlightedLine = $line;
	}
	return $highlightedLine;
}

function mb_substr_replace($string, $replacement, $start, $length=NULL) {
    if (is_array($string)) {
        $num = count($string);
        // $replacement
        $replacement = is_array($replacement) ? array_slice($replacement, 0, $num) : array_pad(array($replacement), $num, $replacement);
        // $start
        if (is_array($start)) {
            $start = array_slice($start, 0, $num);
            foreach ($start as $key => $value)
                $start[$key] = is_int($value) ? $value : 0;
        }
        else {
            $start = array_pad(array($start), $num, $start);
        }
        // $length
        if (!isset($length)) {
            $length = array_fill(0, $num, 0);
        }
        elseif (is_array($length)) {
            $length = array_slice($length, 0, $num);
            foreach ($length as $key => $value)
                $length[$key] = isset($value) ? (is_int($value) ? $value : $num) : 0;
        }
        else {
            $length = array_pad(array($length), $num, $length);
        }
        // Recursive call
        return array_map(__FUNCTION__, $string, $replacement, $start, $length);
    }
    preg_match_all('/./us', (string)$string, $smatches);
    preg_match_all('/./us', (string)$replacement, $rmatches);
    if ($length === NULL) $length = mb_strlen($string);
    array_splice($smatches[0], $start, $length, $rmatches[0]);
    return join($smatches[0]);
}
?>