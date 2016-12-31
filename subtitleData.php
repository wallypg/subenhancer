<?php 
// $_POST['info_url'] = 'https://www.tusubtitulo.com/serie/the-walking-dead/7/7/750/';
// $_POST['sub_url'] = 'https://www.tusubtitulo.com/updated/5/52771/0';

$validUrlPatternInfo = '#^https://www.tusubtitulo.com/serie/[^/]+/[0-9]+/[0-9]+/[0-9]+/$#';
$validUrlPatternInfo2 = '#^https://www.tusubtitulo.com/episodes/[0-9]+/[^/]+(/)?$#';
$validUrlPatternSub = '#^https://www.tusubtitulo.com/[^/]+/[0-9]+/[0-9]+(/[0-9]+)?$#';

if(isset($_POST['info_url']) && (preg_match($validUrlPatternInfo, $_POST['info_url']) || preg_match($validUrlPatternInfo2, $_POST['info_url']) )) {
	$infoUrl = $_POST['info_url'];
	if(isset($_POST['sub_url']) && preg_match($validUrlPatternSub, $_POST['sub_url'])) 
		$subUrl = str_replace('https://www.tusubtitulo.com/', '', $_POST['sub_url']);
} else die ('URL INVALIDA');

// CURL
// $curlResource=curl_init();
// curl_setopt_array($curlResource, array(
//   CURLOPT_RETURNTRANSFER => 1,
//   CURLOPT_URL => $infoUrl
// ));
// $curlResult = curl_exec($curlResource);
require ('user_agent.php');
$user_agent = random_user_agent();

$ch=curl_init();
$proxyServer = rand(1,7);
$url = 'https://'.$proxyServer.'.hidemyass.com/includes/process.php?action=update&u='.urlencode($_POST['info_url']);
// https://3.hidemyass.com/includes/process.php?action=update&u=https://www.tusubtitulo.com/episodes/53173/homeland-6x01-fair-game

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

$curlResult = curl_exec($ch);


if(!curl_exec($ch)){
  die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
}
curl_close($ch);

// Objeto DOM
$dom = new domDocument;
libxml_use_internal_errors(true);
$dom->loadHTML($curlResult); 
$dom->preserveWhiteSpace = false;
$finder = new DomXPath($dom);


$titleId = 'cabecera-subtitulo';
$episodeId = 'ssheader';

$titleString = trim(getElementById($finder,$titleId)->nodeValue);
$rawEpisodeString = trim(getElementById($finder,$episodeId)->nodeValue);

preg_match('/[0-9]{2}\:[0-9]{2}\:[0-9]{2}(?=\s*$)/', $rawEpisodeString, $episodeLength);
$rawEpisodeString = str_replace($episodeLength[0],'',$rawEpisodeString);
$episodeString = trim($rawEpisodeString);

preg_match('/(?<=Temporada )[0-9]+(?=,)/', $episodeString, $seasonNumber);
preg_match('/(?<=Capítulo )[0-9]+/', $episodeString, $episodeNumber);

$tvShow = strstr($episodeString, ',', true);
$episodeTitle = substr(strstr($titleString, ' - '), 3);
$episodeLength = $episodeLength[0];
$seasonNumber = $seasonNumber[0];
$episodeNumber = $episodeNumber[0];

$data = array('tv_show'=>$tvShow, 'season'=>$seasonNumber, 'episode_number'=>$episodeNumber, 'episode_title'=>$episodeTitle, 'duration'=>$episodeLength);

if(isset($subUrl)) {
	$nodes = getElementByClass($finder, 'ssdiv');
	for ($i = $nodes->length - 1; $i > -1; $i--) {
		$versionNode = $nodes->item($i);
		$arrayVersions[] = $versionNode->ownerDocument->saveHTML($versionNode);
	}
	// print_r($arrayVersions);
	foreach($arrayVersions as $version)
		if(strpos($version, $subUrl) !== false) {
			preg_match('/(?<=Versión ).+(?=,)/', $version, $group);
			$data['group'] = $group[0];
		}
}

echo json_encode($data);
die();

function getElementById($finder,$id) {
    return $finder->query("//*[@id='$id']")->item(0);
}

function getElementByClass($finder,$class) {
    return $finder->query("//div[contains(concat(' ', @class, ' '), ' $class ')]");
}

?>