<?php
// header('Content-Type: text/html; charset=utf-8');
$uploadOk = 1;

// Check file size
if ($_FILES["uploadedFile"]["size"] > 300000) {
	echo "Sorry, your file is too large.<br />";
	$uploadOk = 0;
}

// Allow certain file formats
$fileType = pathinfo($_FILES['uploadedFile']['name'],PATHINFO_EXTENSION);
if($fileType != "srt" ) {
	echo "Sorry, only SRT files are allowed.<br />";
	$uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) exit();

$fileContent = file_get_contents($_FILES['uploadedFile']['tmp_name']);
$fileContent = utf8_encode ( $fileContent );
// $fileContent = mb_convert_encoding($fileContent, 'HTML-ENTITIES', "UTF-8");

$subtitle = new stdClass();

// segmento -> conjunto de 3 lineas {secuencia, tiempo, texto}

foreach(preg_split("/\n\s*\n/s", $fileContent) as $segmentKey => $segment){
	$segmentObject = new stdClass();
	$segmentObject->sequence = $segmentKey+1;
	$segmentArray = array();
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $segment) as $key => $line){
		if(preg_match('/\d{2}:\d{2}:\d{2},\d{3} --> \d{2}:\d{2}:\d{2},\d{3}/',$line)) {
			sscanf($line, "%d:%d:%d,%d --> %d:%d:%d,%d",$startHour,$startMinute,$startSecond,$startMillisecond,$endHour,$endMinute,$endSecond,$endMillisecond);
			$segmentObject->startHour = $startHour;
			$segmentObject->startMinute = $startMinute;
			$segmentObject->startSecond = $startSecond;
			$segmentObject->startMillisecond = $startMillisecond;
			$segmentObject->endHour = $endHour;
			$segmentObject->endMinute = $endMinute;
			$segmentObject->endSecond = $endSecond;
			$segmentObject->endMillisecond = $endMillisecond;
			
			$segmentObject->startTimeInMilliseconds = calculateMilliseconds($startHour,$startMinute,$startSecond,$startMillisecond);
			$segmentObject->endTimeInMilliseconds = calculateMilliseconds($endHour,$endMinute,$endSecond,$endMillisecond);
			$segmentObject->sequenceDuration = $segmentObject->endTimeInMilliseconds - $segmentObject->startTimeInMilliseconds;
		}
		$segmentArray[$key] = $line;
	}
	$segmentObject->totalCharacters = 0;
	for($i=2; $i<count($segmentArray)-1; $i++) {
		$segmentObject->totalCharacters += mb_strlen($segmentArray[$i]);
	}
	if(isset($segmentObject->sequenceDuration) && isset($segmentObject->totalCharacters)) $segmentObject->cps = calculateCps($segmentObject->sequenceDuration, $segmentObject->totalCharacters);	
	$subtitle->$segmentKey = $segmentObject;

	if(isset($segmentObject->sequenceDuration)) {
		// echo formatMilliseconds($segmentObject->startTimeInMilliseconds).' --> '.formatMilliseconds($segmentObject->endTimeInMilliseconds).'<br>';
	}
}





// print_r($subtitle);

// ---------------------- Functions ---------------------- //

function calculateMilliseconds($hour,$minute,$second,$millisecond) {
	$totalMilliseconds = $hour*3600000+$minute*60000+$second*1000+$millisecond;
	return $totalMilliseconds;
}

function calculateCps($duration,$characters) {
	$cps = round($characters/($duration/1000),2);
	return $cps;
}

function formatMilliseconds($milliseconds){
	$totalSeconds = $milliseconds/1000;
	$secondsWhole = floor($totalSeconds);
	$secondsFraction = round($totalSeconds - $secondsWhole,3)*1000;

	$hours = floor($secondsWhole / 3600);
	$minutes = floor(($secondsWhole / 60) % 60);
	$seconds = $secondsWhole % 60;

	return sprintf("%02d:%02d:%02d,%03d",$hours,$minutes,$seconds,$secondsFraction);
}

?>