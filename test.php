<?php 
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
$curlResult = mb_convert_encoding($curlResult, 'utf-8', "windows-1252");

print_r($curlResult);

?>