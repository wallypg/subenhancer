<?php 
// Seteo SESSION ID
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, "https://www.tusubtitulo.com/");
// curl_setopt($ch, CURLOPT_HEADER, 0);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// curl_exec($ch);
// print_r($_COOKIE['PHPSESSID']);
// die();
$refererUrl = '';
$curlUrl = 'https://www.tusubtitulo.com/updated/5/52533/0';


$curlResource=curl_init();
curl_setopt($curlResource, CURLOPT_URL, $curlUrl);
curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curlResource, CURLOPT_ENCODING ,"windows-1252");
// curl_setopt($curlResource, CURLOPT_AUTOREFERER, false);
curl_setopt($curlResource, CURLOPT_HTTPHEADER, array(
    // 'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
	// 'Accept-Encoding:gzip, deflate, sdch, br',
	// 'Accept-Language:en-US,en;q=0.8',
	// 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
	// 'Content-type: text/xml;charset=\"windows-1252\"',
	// 'Connection:keep-alive',
	// 'Cookie:'.$_COOKIE['PHPSESSID'],
	// 'Host:www.tusubtitulo.com',
	'Referer:https://www.tusubtitulo.com/'
	// 'Upgrade-Insecure-Requests:1',
	// 'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36'
));

$curlResult = curl_exec($curlResource);
if(!$curlResult){
  die('Error: "' . curl_error($curlResource) . '" - Code: ' . curl_errno($curlResource));
}

$curlResult = mb_convert_encoding($curlResult, 'utf-8', "windows-1252");

// $utf8_string = Encoding::fixUTF8($curlResult);
print_r($curlResult);
// $curlResult = mb_convert_encoding($curlResult, 'utf-8',mb_detect_encoding($curlResult, 'UTF-8, ISO-8859-1', true));
// header('Content-Type: text/html; charset=utf-8');
// $curlResult = utf8_encode ( $curlResult );

// echo nl2br(file_get_contents_utf8('original.srt'));
// $fileContent = mb_convert_encoding($fileContent, 'HTML-ENTITIES', "UTF-8");

// $fileContent = mb_convert_encoding($fileContent, 'HTML-ENTITIES', "UTF-8");
// $fileContent = utf8_encode ( $fileContent );
// print_r (mb_convert_encoding($curlResult, 'UTF-8',mb_detect_encoding($curlResult, 'UTF-8, ISO-8859-1', true)));

function file_get_contents_utf8($fn) {
     $content = file_get_contents($fn);
      return mb_convert_encoding($content, 'UTF-8',
          mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}
?>