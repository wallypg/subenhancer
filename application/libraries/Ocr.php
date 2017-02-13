<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ocr {

  public function ocrCheck($string,$ocr='abcd',$dbg=false)
  {
    $originalString = $string;
    $returnArray = array();
    $matches = array();
    $arrayFindings = array();    

    $openTag = '<span class="ocr-highlight">';
    $closeTag = '</span>';
    $lookAhead = '(?!'.preg_quote($closeTag).')';
    $lookBehind = '(?<!'.preg_quote($openTag).')';

    // $letterArray = array_unique(str_split($ocr));
    $letterArray = str_split($ocr);

    foreach($letterArray as $letter) {
      // a -> ocr-reduce
        if($letter == 'a') $ocr = 'ocr-reduce';
      // b -> ocr-asia-team
        if($letter == 'b') $ocr = 'ocr-asia-team';
      // c -> ocr-reduce-at
        if($letter == 'c') $ocr = 'ocr-reduce-at';
      // d -> ocr-unidades-sentido
        if($letter == 'd') $ocr = 'ocr-unidades-sentido';
      // t -> test
        if($letter == 't') $ocr = 'sense';

      $allOcr = json_decode(file_get_contents('json/'.$ocr.'.json'));
      
      if(isset($allOcr->regex)) {
        foreach($allOcr->regex as $ocr){
            if(!in_array('disabled',$ocr->tags)) {
              // find, replace, useREonlyToFind
              $pattern = '/'.$ocr->find.'/u';
              
              if(preg_match_all($pattern, $string, $matches)) {
                if(!isset($returnArray['regex'])) $returnArray['regex'] = array();
                if(!isset($returnArray['ocrCounter'])) $returnArray['ocrCounter'] = 0;

                $returnArray['ocrCounter'] += 1;

                $regexPattern = '#'.$lookBehind.$ocr->find.$lookAhead.'#u';

                foreach($matches[0] as $match) {
                  $returnArray['found'] = (isset($returnArray['found']))
                                          ?
                                          preg_replace($regexPattern, $openTag.$match.$closeTag, $returnArray['found'], 1)
                                          :
                                          preg_replace($regexPattern, $openTag.$match.$closeTag, $string, 1);
                }

                $ocr->replace = str_replace('\n', "\n", $ocr->replace);

                $returnArray['ocredLine'] = preg_replace($pattern, $ocr->replace, $string);

                foreach($matches[0] as $match) {
                  $returnArray['replaced'] = (isset($returnArray['replaced']))
                                          ?
                                          preg_replace($regexPattern, $openTag.$ocr->replace.$closeTag, $returnArray['replaced'], 1)
                                          :
                                          preg_replace($regexPattern, $openTag.$ocr->replace.$closeTag, $string, 1);
                }

                // $returnArray['found'] = (isset($returnArray['found'])) ? $this->highlightChange($originalString, $matches[0], $returnArray['found']) : $this->highlightChange($originalString, $matches[0]);//h-l
                
                // $returnArray['replaced'] = (isset($returnArray['replaced'])) ? $this->highlightChange($returnArray['ocredLine'], $ocr->replace, $returnArray['replaced']) : $this->highlightChange($returnArray['ocredLine'], $ocr->replace);//h-l

                if($dbg) array_push($returnArray['regex'], $pattern);
                $string = $returnArray['ocredLine'];
              }          
            }
        }      
      }


      if(isset($allOcr->string)) {
        foreach($allOcr->string as $ocr){
          if(!in_array('disabled',$ocr->tags)) {
            // find, replace, preserveCase, caseSensitive, wholeWord
            
            // ((?<=\s|\W)|^) <----> ($|(?=\s|\W))
            // (?:(?<=[\W^¿])(?=[\wá-úÁ-ÚñÑ])|(?<=[\wá-úÁ-ÚñÑ])(?=\W|$)) <----> (?:(?<=\W|^)(?=\w)|(?<=\w)(?=\W|$))
            // (?<=^|\.|\s|,|¿|\?|\¡|\$|\·|\&|\@|\\|\#|\~|\+|\*|\-|\_|\:|\;|\=|\{|\}|\[|\]|\/|\(|\)|\||\%|\<|\>) <-----> (?=^|\.|\s|,|¿|\?|\¡|\$|\·|\&|\@|\\|\#|\~|\+|\*|\-|\_|\:|\;|\=|\{|\}|\[|\]|\/|\(|\)|\||\%|\<|\>)

            $pattern = ($ocr->wholeWord) ? '/((?<=\W|\s|^)|\b)'.preg_quote($ocr->find).'((?=\W|\s|$)|\b)/u' : '/'.preg_quote($ocr->find).'/u';
            if(!$ocr->caseSensitive) $pattern .= 'i';
            if(preg_match($pattern, $string, $matches)) {
              // ¿$string vs $originalString?
              // $regexPattern = '#'.$lookBehind.$ocr->find.$lookAhead.'#u';
              if(!isset($returnArray['ocrCounter'])) $returnArray['ocrCounter'] = 0;
              $returnArray['ocrCounter'] += 1;

              $returnArray['found'] = (isset($returnArray['found']))
                                      ?
                                      preg_replace($pattern, $openTag.$matches[0].$closeTag, $returnArray['found'])
                                      :
                                      preg_replace($pattern, $openTag.$matches[0].$closeTag, $string);

                // $returnArray['found'] = (isset($returnArray['found'])) ? $this->highlightChange($originalString, $matches[0], $returnArray['found']) : $this->highlightChange($originalString, $matches[0]);//h-l

                if($ocr->preserveCase) {
                    // $ocr->replace = (startsWithUpper($matches[0])) ? ucfirst($ocr->replace) : lcfirst($ocr->replace) ;
                    $ocr->replace = ($this->startsWithUpper($matches[0])) ? $this->firstLetterCase($ocr->replace,'u') : $this->firstLetterCase($ocr->replace,'l');
                }

                $returnArray['ocredLine'] = preg_replace($pattern, $ocr->replace, $string);

                $returnArray['replaced'] = (isset($returnArray['replaced']))
                                      ?
                                      preg_replace($pattern, $openTag.$ocr->replace.$closeTag, $returnArray['replaced'])
                                      :
                                      preg_replace($pattern, $openTag.$ocr->replace.$closeTag, $string);

                // $returnArray['replaced'] = (isset($returnArray['replaced'])) ? $this->highlightChange($returnArray['ocredLine'], $ocr->replace, $returnArray['replaced']) : $this->highlightChange($returnArray['ocredLine'], $ocr->replace);//h-l
                $string = $returnArray['ocredLine'];

            }
          }
        }
      }

      // NUEVO RESALTADO (LIBRERÍA DiffString -> https://github.com/sdelamorena/php-string-diff)
      // if(isset($returnArray['ocredLine'])) {
      //   $this->_CI =& get_instance();
      //   $this->_CI->load->library("diffstring");
        
      //   $diffsArray = $this->_CI->diffstring->compare($originalString,$returnArray['ocredLine']);
      //   $returnArray['found'] = $this->highlightDiff($originalString, $diffsArray['stop']);
      //   $returnArray['replaced'] = $this->highlightDiff($returnArray['ocredLine'], $diffsArray['start']);
      // }

      // if(isset($returnArray['found']) && !strpos('</span>', $returnArray['found'])) $returnArray['found'] = highlightFirstLetter($returnArray['found']);
      // if(isset($returnArray['replaced']) && !strpos('</span>', $returnArray['replaced'])) $returnArray['replaced'] = highlightFirstLetter($returnArray['replaced']);
    }    
    return $returnArray;

  }

  public function startsWithUpper($str) {
      $alphaChr = false;
      for($i = 0; $i < strlen($str) && !$alphaChr; $i++) {
          $chr = mb_substr ($str, $i, 1, "windows-1252"); 
          $alphaChr = ctype_alpha($chr);
      }
      
      return mb_strtolower($chr, "windows-1252") != $chr;
  }
  
  public function firstLetterCase ($str,$case) {
      $alphaChr = false;
      for($i = 0; $i < strlen($str) && !$alphaChr; $i++) {
          $chr = mb_substr ($str, $i, 1, "windows-1252"); 
          $alphaChr = ctype_alpha($chr);
      }
      // $caseIndex = (0) ? 0 : $i-1;
      $str[$i-1] = ($case == 'u') ? ucfirst($str[$i-1]) : lcfirst($str[$i-1]);
      return $str;
  }

  public function highlightChange($line, $finding, $alreadyHighlighted = NULL) {
    $openTag = '<span class="ocr-highlight">';
    $closeTag = '</span>';
   
    $highlightedLine = (!is_null($alreadyHighlighted)) ? str_ireplace($finding, $openTag.$finding.$closeTag, $line) : str_replace($finding, $openTag.$finding.$closeTag, $line);
    // $highlightedLine = str_replace($finding, $openTag.$finding.$closeTag, $line);
    if(!is_null($alreadyHighlighted)) {
        $originalOpenTagPosition = mb_strpos ($alreadyHighlighted, $openTag);
        $originalCloseTagPosition = mb_strpos (str_replace($openTag, '', $alreadyHighlighted), $closeTag, $originalOpenTagPosition);
        $newOpenTagPosition = mb_strpos ($highlightedLine, $openTag);
        $newCloseTagPosition = mb_strpos (str_replace($openTag, '', $highlightedLine), $closeTag, $newOpenTagPosition);
        
        // EQUALS??
        // CASE SENSITIVITY
        // MB REPLACE

        if($newOpenTagPosition >= $originalOpenTagPosition && $newOpenTagPosition <= $originalCloseTagPosition) {
            // $line = substr_replace($line, $closeTag, $originalCloseTagPosition, 0);
            $line = ($newCloseTagPosition >= $originalCloseTagPosition) ? substr_replace($line, $closeTag, $newCloseTagPosition+1, 0) : substr_replace($line, $closeTag, $originalCloseTagPosition+1, 0);
            $line = substr_replace($line, $openTag, $originalOpenTagPosition, 0);
        } else if($newOpenTagPosition >= $originalCloseTagPosition) {
            // +1?
            $line = substr_replace($line, $closeTag, $newCloseTagPosition, 0);
            $line = substr_replace($line, $openTag, $originalOpenTagPosition, 0);
        } else if($newOpenTagPosition <= $originalOpenTagPosition) {
            // +1?
            $line = ($newCloseTagPosition >= $originalCloseTagPosition) ? $this->mb_substr_replace($line, $closeTag, $newCloseTagPosition, 0) : $this->mb_substr_replace($line, $closeTag, $originalCloseTagPosition, 0);
            $line = substr_replace($line, $openTag, $newOpenTagPosition, 0);
        }
        $highlightedLine = $line;
    }
    return $highlightedLine;
  }

  // substr_replace multibyte
  public function mb_substr_replace($string, $replacement, $start, $length=NULL) {
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

  public function highlightDiff($string, $diffs) {
    $openTag = '<span class="ocr-highlight">';
    $closeTag = '</span>';

    $diffArray = explode(' ', $diffs);

    foreach ($diffArray as $key => $diff) {
      $continue = 1;
      for($i = 1; $i < count($diffArray) && $continue; $i++) {
        if(isset($diffArray[$i]) && strpos($string, $diff.' '.$diffArray[$i]) !== False) {
          $diff = $diff.' '.$diffArray[$i];
          unset($diffArray[$i]);
        } else $continue = 0;
      }
      $string = str_replace(trim($diff), $openTag.trim($diff).$closeTag, $string);
    }

    $string = str_replace($closeTag.' '.$openTag, ' ', $string);
    return $string;
  }
}

?>