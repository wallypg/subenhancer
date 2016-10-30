<?php
// header('Content-Type: text/html; charset=utf-8');
if(!isset($_FILES["uploadedFile"])) {header('Location: index.html');exit();}
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




/************************************************************/
/**************** Parsing string into object ****************/
/************************************************************/

$subtitle = new stdClass();
$totalLinesOverCps = array();

// segmento -> conjunto de 3 lineas {secuencia, tiempo, texto}

foreach(preg_split("/\n\s*\n/s", $fileContent) as $segmentKey => $segment){
	$segmentObject = new stdClass();
	$segmentObject->sequence = $segmentKey+1;
	$segmentArray = array();
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $segment) as $key => $line){
		// Guardo temporalmente cada linea del segmento en un array
		$segmentArray[$key] = $line;
		
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
	}

	$segmentObject->totalCharacters = 0;
	for($i=2; $i<count($segmentArray)-1; $i++) {
		$textLine = 'textLine'.($i-1);
		$segmentObject->$textLine = $segmentArray[$i];
		$segmentObject->totalCharacters += mb_strlen($segmentArray[$i]);
	}

	
	if(isset($segmentObject->sequenceDuration) && isset($segmentObject->totalCharacters)) {
		$segmentObject->cps = calculateCps($segmentObject->sequenceDuration, $segmentObject->totalCharacters);
		if($segmentObject->cps > 25) array_push($totalLinesOverCps, $segmentKey);
	}
	if($segmentObject->totalCharacters>0) $subtitle->$segmentKey = $segmentObject;
}

/* Object properties */
// [sequence]
// [startHour]
// [startMinute]
// [startSecond]
// [startMillisecond]
// [endHour]
// [endMinute]
// [endSecond]
// [endMillisecond]
// [startTimeInMilliseconds]
// [endTimeInMilliseconds]
// [sequenceDuration]
// [totalCharacters]
// [textLine1] (...)
// [cps]


/************************************************************/
/************************ Scan object ***********************/
/************************************************************/

echo '<h1>Lineas que superaban los 25 CPS: '.count($totalLinesOverCps).'</h1>';

// Optimization magic begins :)
foreach ($totalLinesOverCps as $lineOverCps) {
	fillEmptySpace($subtitle,$lineOverCps);
}
$totalLinesOverCps = checkLinesOverCps($subtitle,$totalLinesOverCps);

echo '<h1>Lineas que superan los 25 CPS después de la optimización: '.count($totalLinesOverCps).'</h1>';



/************************************************************/
/********************* Subtitle rebuild *********************/
/************************************************************/

foreach ($subtitle as $thisSegmentKey => $segment) {
	echo $segment->sequence;
	echo '<br />';
	echo formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds);
	echo '<br />';
	if(isset($segment->textLine1)) echo $segment->textLine1.'<br />';
	if(isset($segment->textLine2)) echo $segment->textLine2.'<br />';
	if(isset($segment->textLine3)) echo $segment->textLine3.'<br />';
	echo '<br />';
}


// print_r($subtitle);


















/************************************************************/
/************************* Functions ************************/
/************************************************************/

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

function fillEmptySpace ($subtitle,$thisSequence) {
	echo 'Sequence: '.$subtitle->$thisSequence->sequence.'<br>';
	echo 'Sequence duration: '.$subtitle->$thisSequence->sequenceDuration.'<br>';
	echo 'Needed time: '.checkNeededTime($subtitle->$thisSequence).'<br>';
	echo 'Missing time: '.checkMissingTime($subtitle->$thisSequence).'<br>';
	echo '<br>';

	$previousSequence = $thisSequence - 1;
	$nextSequence = $thisSequence +1;

	// Corregir aumento de duracion a tiempo necesario

	if(property_exists($subtitle,$previousSequence)) {
		if($subtitle->$thisSequence->startTimeInMilliseconds - $subtitle->$previousSequence->endTimeInMilliseconds > 3000) {
			$subtitle->$thisSequence->startTimeInMilliseconds -= 3000;
		} else {
			$subtitle->$thisSequence->startTimeInMilliseconds = $subtitle->$previousSequence->endTimeInMilliseconds+1;
		}
	} else {
		// Si es la primera linea
	}

	if(property_exists($subtitle,$nextSequence)) {
		if($subtitle->$nextSequence->startTimeInMilliseconds - $subtitle->$thisSequence->endTimeInMilliseconds > 3000) {
			$subtitle->$thisSequence->endTimeInMilliseconds += 3000;
		} else {
			$subtitle->$thisSequence->endTimeInMilliseconds = $subtitle->$nextSequence->startTimeInMilliseconds-1;
		}
	} else {
		// Si es la última línea
		$subtitle->$thisSequence->endTimeInMilliseconds += 3000;
	}

	// Update sequence duration
	updateSequenceData($subtitle,$thisSequence);
}

function updateSequenceData ($subtitle,$sequence) {
	$subtitle->$sequence->sequenceDuration = $subtitle->$sequence->endTimeInMilliseconds - $subtitle->$sequence->startTimeInMilliseconds;
	$subtitle->$sequence->cps = calculateCps($subtitle->$sequence->sequenceDuration,$subtitle->$sequence->totalCharacters);
	// updateSequenceTimes('start',$subtitle,$sequence);
	// updateSequenceTimes('end',$subtitle,$sequence);
}

function checkNeededTime ($segment) {
	return floor(25*$segment->sequenceDuration/$segment->cps);
}

function checkMissingTime ($segment) {
	return floor(25*$segment->sequenceDuration/$segment->cps) - $segment->sequenceDuration;
}

function checkLinesOverCps ($subtitle,$totalLinesOverCps) {
	foreach ($totalLinesOverCps as $key => $lineOverCps) {
		if($subtitle->$lineOverCps->cps <= 25) unset($totalLinesOverCps[$key]);
	}
	return $totalLinesOverCps;
}

// function updateSequenceTimes ($timeType,$subtitle,$sequence) {
// 	$timeTypeDuration = $timeType.'TimeInMilliseconds';
// 	$hourType = $timeType.'Hour';
// 	$minuteType = $timeType.'Minute';
// 	$secondType = $timeType.'Second';
// 	$millisecondType = $timeType.'Millisecond';

// 	$totalSeconds = $subtitle->$sequence->$timeTypeDuration/1000;
// 	$secondsWhole = floor($totalSeconds);
// 	$secondsFraction = round($totalSeconds - $secondsWhole,3)*1000;

// 	$hours = floor($secondsWhole / 3600);
// 	$minutes = floor(($secondsWhole / 60) % 60);
// 	$seconds = $secondsWhole % 60;

// 	$subtitle->$sequence->$hourType = $hours;
// 	$subtitle->$sequence->$minuteType = $minutes;
// 	$subtitle->$sequence->$secondType = $seconds;
// 	$subtitle->$sequence->$millisecondType = $secondsFraction;
// }



// Upcoming fixes: Lines under  second
?>