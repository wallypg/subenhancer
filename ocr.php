<?php 

// MODO "DEBUG OCR"
if(isset($_GET['dbg'])) {
	if(isset($_GET['filename']) && !empty($_GET['filename'])) {
		$file = 'srt/original/'.$_GET['filename'].'.srt';
		if(file_exists(utf8_decode($file))) {
			$handle = fopen(utf8_decode($file), "r");
			if ($handle) {
			    echo '<style>.ocr-highlight{background-color: #ffff00}</style>';
			    while (($line = fgets($handle)) !== false) {
			        $line = mb_convert_encoding($line, 'utf-8', "windows-1252");
			    	if(!preg_match('/\d{2}\:\d{2}\:\d{2}\,\d{3}\s-->\s\d{2}\:\d{2}\:\d{2}\,\d{3}/',$line)) {
						echo 'Frase:<br>'.$line.'<br>';
						$ocrResult = ocrCheck($line);
						if(isset($ocrResult['ocredLine'])) echo '<br><br>Resultado:<br>'.$ocrResult['ocredLine'];
						else echo 'Sin optimización.';
						echo '<br><br>Array retornado:<br>';
						echo '<pre>';
						print_r($ocrResult);
						echo '</pre>';
						echo '<hr>';
			    	}
			    }
			    fclose($handle);
			} else die('Error abriendo el archivo');
	    } else die('Archivo no encontrado.');
	} elseif(isset($_GET['string']) && !empty($_GET['string'])) {
		echo '<style>.ocr-highlight{background-color: #ffff00}</style>';
		$string = $_GET['string'];
		echo 'Frase:<br>'.$string.'<br>';
		$ocrResult = ocrCheck($string);
		if(isset($ocrResult['ocredLine'])) echo '<br><br>Resultado:<br>'.$ocrResult['ocredLine'];
		else echo 'Sin optimización.';
		echo '<br><br>Array retornado:<br>';
		echo '<pre>';
		print_r($ocrResult);
		echo '</pre>';
		die();
	} else die('Agregar el parámetro "string" para comprobar una frase o "filename" para escanear líneas de un archivo.');
}

function ocrCheck($string) {
	$allOcr = json_decode(file_get_contents('json/ocr_v2.json'));
	$returnArray = array();
	$matches = array();
	
	$originalString = $string;
	foreach($allOcr->regex as $ocr){
		// find, replace, useREonlyToFind
		$pattern = '/'.$ocr->find.'/u';
		// if($ocr->global) echo 'a';
		// $pregMatch = ($ocr->global) ? preg_match_all($pattern, $string, $matches) : preg_match($pattern, $string, $matches);
		if(preg_match($pattern, $string, $matches)) {
			$returnArray['found'] = (isset($returnArray['found'])) ? highlightChange($originalString, $matches[0], $returnArray['found']) : highlightChange($originalString, $matches[0]);
			$returnArray['ocredLine'] = preg_replace($pattern, $ocr->replace, $string);
			$returnArray['replaced'] = (isset($returnArray['replaced'])) ? highlightChange($returnArray['ocredLine'], $ocr->replace, $returnArray['replaced']) : highlightChange($returnArray['ocredLine'], $ocr->replace);
			$string = $returnArray['ocredLine'];
		}
	}

	foreach($allOcr->string as $ocr){
		// find, replace, preserveCase, caseSensitive, wholeWord
		// ((?<=\s|\W)|^) <----> ($|(?=\s|\W))
		// (?:(?<=[\W^¿])(?=[\wá-úÁ-ÚñÑ])|(?<=[\wá-úÁ-ÚñÑ])(?=\W|$)) <----> (?:(?<=\W|^)(?=\w)|(?<=\w)(?=\W|$))
		// (?<=^|\.|\s|,|¿|\?|\¡|\$|\·|\&|\@|\\|\#|\~|\+|\*|\-|\_|\:|\;|\=|\{|\}|\[|\]|\/|\(|\)|\||\%|\<|\>) <-----> (?=^|\.|\s|,|¿|\?|\¡|\$|\·|\&|\@|\\|\#|\~|\+|\*|\-|\_|\:|\;|\=|\{|\}|\[|\]|\/|\(|\)|\||\%|\<|\>)
		$pattern = ($ocr->wholeWord) ? '/((?<=\W|\s|^)|\b)'.preg_quote($ocr->find).'((?=\W|\s|$)|\b)/u' : '/'.preg_quote($ocr->find).'/u';
		if(!$ocr->caseSensitive) $pattern .= 'i';
        if(preg_match($pattern, $string, $matches)) {
			// ¿$string vs $originalString?
			$returnArray['found'] = (isset($returnArray['found'])) ? highlightChange($originalString, $matches[0], $returnArray['found']) : highlightChange($originalString, $matches[0]);

			if($ocr->preserveCase) {
                // $ocr->replace = (startsWithUpper($matches[0])) ? ucfirst($ocr->replace) : lcfirst($ocr->replace) ;
                $ocr->replace = (startsWithUpper($matches[0])) ? firstLetterCase($ocr->replace,'u') : firstLetterCase($ocr->replace,'l');
			}

			$returnArray['ocredLine'] = preg_replace($pattern, $ocr->replace, $string);
			$returnArray['replaced'] = (isset($returnArray['replaced'])) ? highlightChange($returnArray['ocredLine'], $ocr->replace, $returnArray['replaced']) : highlightChange($returnArray['ocredLine'], $ocr->replace);
			$string = $returnArray['ocredLine'];

		}
	}

	// if(isset($returnArray['found']) && !strpos('</span>', $returnArray['found'])) $returnArray['found'] = highlightFirstLetter($returnArray['found']);
	// if(isset($returnArray['replaced']) && !strpos('</span>', $returnArray['replaced'])) $returnArray['replaced'] = highlightFirstLetter($returnArray['replaced']);
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

function firstLetterCase ($str,$case) {
	$alphaChr = false;
	for($i = 0; $i < strlen($str) && !$alphaChr; $i++) {
		$chr = mb_substr ($str, $i, 1, "windows-1252");	
		$alphaChr = ctype_alpha($chr);
	}
	// $caseIndex = (0) ? 0 : $i-1;
	$str[$i-1] = ($case == 'u') ? ucfirst($str[$i-1]) : lcfirst($str[$i-1]);
	return $str;
}



function highlightChange($line, $finding, $alreadyHighlighted = NULL) {
	$openTag = '<span class="ocr-highlight">';
	$closeTag = '</span>';

	// echo($line);
	// echo "   ->   ";
	// echo($alreadyHighlighted);
	// echo "   ->   ";
	// echo($finding);
	// echo "   ->   ";
	
	$highlightedLine = (!is_null($alreadyHighlighted)) ? str_ireplace($finding, $openTag.$finding.$closeTag, $line) : str_replace($finding, $openTag.$finding.$closeTag, $line);
	// $highlightedLine = str_replace($finding, $openTag.$finding.$closeTag, $line);
	if(!is_null($alreadyHighlighted)) {
		$originalOpenTagPosition = mb_strpos ($alreadyHighlighted, $openTag);
		$originalCloseTagPosition = mb_strpos (str_replace($openTag, '', $alreadyHighlighted), $closeTag, $originalOpenTagPosition);
		$newOpenTagPosition = mb_strpos ($highlightedLine, $openTag);
		$newCloseTagPosition = mb_strpos (str_replace($openTag, '', $highlightedLine), $closeTag, $newOpenTagPosition);
		// echo $newOpenTagPosition.'<br/>';
		// echo $newCloseTagPosition.'<br/>';
		
		// EQUALS??
		// CASE SENSITIVITY
		// MB REPLACE

		if($newOpenTagPosition >= $originalOpenTagPosition && $newOpenTagPosition <= $originalCloseTagPosition) {
			// $line = substr_replace($line, $closeTag, $originalCloseTagPosition, 0);
			// die();
			$line = ($newCloseTagPosition >= $originalCloseTagPosition) ? substr_replace($line, $closeTag, $newCloseTagPosition+1, 0) : substr_replace($line, $closeTag, $originalCloseTagPosition+1, 0);
			$line = substr_replace($line, $openTag, $originalOpenTagPosition, 0);
		} else if($newOpenTagPosition >= $originalCloseTagPosition) {
			// +1?
			$line = substr_replace($line, $closeTag, $newCloseTagPosition, 0);
			$line = substr_replace($line, $openTag, $originalOpenTagPosition, 0);
		} else if($newOpenTagPosition <= $originalOpenTagPosition) {
			// +1?
			$line = ($newCloseTagPosition >= $originalCloseTagPosition) ? mb_substr_replace($line, $closeTag, $newCloseTagPosition, 0) : mb_substr_replace($line, $closeTag, $originalCloseTagPosition, 0);
			$line = substr_replace($line, $openTag, $newOpenTagPosition, 0);
		}
		$highlightedLine = $line;
	}

	// echo($highlightedLine);
	// echo "\n";
	return $highlightedLine;
}

// function highlightFirstLetter($str) {
// 	$openTag = '<span class="ocr-highlight">';
// 	$closeTag = '</span>';

// 	$alphaChr = false;
// 	for($i = 0; $i < strlen($str) && !$alphaChr; $i++) {
// 		$chr = mb_substr ($str, $i, 1, "windows-1252");	
// 		$alphaChr = ctype_alpha($chr);
// 	}
// 	echo $alphaChr;

// 	$str[$i-1] = $openTag.$str[$i-1].$closeTag;
// 	return $str;

	
// 	return $highlightedLine;
// }

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