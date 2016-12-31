<?php 
	$url = 'https://www.tusubtitulo.com/updated/6/52124/0';
	// $url = 'https://5.hidemyass.com/includes/process.php?action=update&u='.urlencode('https://www.tusubtitulo.com/updated/6/52124/0');
	$url = 'https://googleweblight.com/?lite_url='.$url.'&s=1&f=1&host=www.google.ie';

    // $ch=curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_ENCODING ,"windows-1252");
    curl_setopt($ch, CURLOPT_REFERER, 'https://www.tusubtitulo.com/');


    $curlResult = curl_exec($ch);
    
    if(!$curlResult){
        // ERROR VIEW
        $error = 'Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch);
        die();
    }

    $curlResult = mb_convert_encoding($curlResult, 'utf-8', "windows-1252");
    echo $curlResult;


     ?>