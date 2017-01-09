<?php 
/**************************************************************/
/*
/*                 ENTRADA / SALIDA DE SUBTÍTULOS
/*
/**************************************************************/

// Muestra el subtítulo optimizado en pantalla
function printEnhancedSubtitle ($subtitle,$totalSequences) {
    foreach ($subtitle as $thisSegmentKey => $segment) {
        /* Reconstrucción del subtítulo */
        echo $segment->sequence;//ss
        echo '<br />';//ss
        echo formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds);//ss
        echo '<br />';//ss
        if(isset($segment->textLine1)) echo $segment->textLine1.'<br />';//ss
        if(isset($segment->textLine2)) echo $segment->textLine2.'<br />';//ss
        if(isset($segment->textLine3)) echo $segment->textLine3.'<br />';//ss
        echo '<br />';//ss
    }
    echo ($totalSequences+1)."<br />04:08:15,016 --> 04:08:23,420<br />Enhanced with Love in SubAdictos.net<br />";
}

// Muestra el subtítulo optimizado en pantalla
function downloadEnhancedSubtitle ($subtitle,$totalSequences,$filename) {
    $subtitleString = '';
    foreach ($subtitle as $thisSegmentKey => $segment) {
        $sequenceString = $segment->sequence."\r\n";//sf
        $sequenceString .= formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds)."\r\n";//sf
        if(isset($segment->textLine1)) $sequenceString .= utf8_decode($segment->textLine1)."\r\n";//sf
        if(isset($segment->textLine2)) $sequenceString .= utf8_decode($segment->textLine2)."\r\n";//sf
        if(isset($segment->textLine3)) $sequenceString .= utf8_decode($segment->textLine3)."\r\n";//sf
        $sequenceString .= "\r\n";//sf
        $subtitleString .= $sequenceString;//sf
    }
    $subtitleString .= ($totalSequences+1)."\r\n04:08:15,016 --> 04:08:23,420\r\nEnhanced with Love in SubAdictos.net\r\n";


    /* Descarga del subtitítulo optimizado */
    header("Content-Type: text/plain;charset=windows-1252");//sf
    header('Content-Disposition: attachment; filename="'.$filename.'"');//sf
    header("Content-Length: " . strlen($subtitleString));//sf
    echo $subtitleString;//sf
}

// Guarda el subtítulo en el servidor
function saveEnhancedSubtitle ($subtitle,$totalSequences,$filename) {
    $subtitleString = '';
    foreach ($subtitle as $thisSegmentKey => $segment) {
        $sequenceString = $segment->sequence."\r\n";//sf
        $sequenceString .= formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds)."\r\n";//sf
        if(isset($segment->textLine1)) $sequenceString .= utf8_decode($segment->textLine1)."\r\n";//sf
        if(isset($segment->textLine2)) $sequenceString .= utf8_decode($segment->textLine2)."\r\n";//sf
        if(isset($segment->textLine3)) $sequenceString .= utf8_decode($segment->textLine3)."\r\n";//sf
        $sequenceString .= "\r\n";//sf
        $subtitleString .= $sequenceString;//sf
    }
    $subtitleString .= ($totalSequences+1)."\r\n04:08:15,016 --> 04:08:23,420\r\nEnhanced with Love in SubAdictos.net\r\n";

    $filename = uniqid('subtitle-');
    deleteTemporaryFiles();
    file_put_contents('srt/enhanced/'.$filename.'.srt', $subtitleString);
    return $filename;
}

// Recibe la url de un subtítulo de "tusubtitulo" y devuelve el subtítulo en un string.
function getSubtitleFromUrl($url) {
    $error = array('error'=>true);
    require ('modules/ua.php');
    $url = str_replace('https://www.tusubtitulo.com', '', $url);
    // $refererUrl = 'https://www.tusubtitulo.com/serie/star-wars-rebels/3/8/2235/';
    // $curlUrl = 'https://www.tusubtitulo.com/updated/5/52632/0';
    // http://www.tusubtitulo.com.https.w1.wbprx.com/original/53312/0
    // https://www.tusubtitulo.com/original/53312/0
    $serversArray = array('w1','s11','s93','s71');
    $server = $serversArray[mt_rand(0, count($serversArray) - 1)];
    $userAgent = randomUserAgent();
    $proxyUrl = 'http://www.tusubtitulo.com.https.'.$server.'.wbprx.com'.$url;


    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $proxyUrl); //orig
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //orig
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING ,"windows-1252"); //orig
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.tusubtitulo.com.https.'.$server.'.wbprx.com/'); //orig

    $curlResult = curl_exec($ch);
    if(!$curlResult){
        // ERROR
        $error['tuSubtitleCurl'] = 'Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch);
        die(json_encode($error));  
    }
    if(strpos($curlResult,"1\n") != 1) $curlResult = "1\n".$curlResult;
    $curlResult = mb_convert_encoding($curlResult, 'utf-8', "windows-1252");
    return $curlResult;
}

// Recibe la url de un subtítulo srt y lo devuelve como un string.
function getSrtSubtitle($url) {
    $error = array('error'=>true);
    require ('modules/ua.php');
    $userAgent = randomUserAgent();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //orig
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING ,"windows-1252"); //orig

    $curlResult = curl_exec($ch);
    if(!$curlResult){
        // ERROR
        $error['srtSubtitleCurl'] = 'Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch);
        die(json_encode($error));  
    }
    $curlResult = mb_convert_encoding($curlResult, 'utf-8', "windows-1252");
    return $curlResult;
}

// Recibe un nombre de un archivo de subtítulo y si se encuentra en el servidor devuelve el contenido como un string.
function getInternalSubtitle($filename) {
    $error = array('error'=>true);
    $file = 'srt/original/'.((preg_match('/\.srt$/',$filename)) ? $filename : $filename.'.srt');
    if(file_exists(utf8_decode($file))) {
        $content = file_get_contents(utf8_decode($file));
        return mb_convert_encoding($content, 'utf-8', "windows-1252");
    } else {
        $error['missingFile'] = 'No existe el archivo en el servidor';
        die(json_encode($error));  
    }
}

// Elimina subtítulos temporales en el servidor.
function deleteTemporaryFiles() {
    $files = glob('srt/enhanced/*.srt');
    $now   = time();
    foreach($files as $file){
        if(is_file($file)) {
            if ($now - filemtime($file) >= 60 * 10)
            unlink($file);
        }
    }
}

?>