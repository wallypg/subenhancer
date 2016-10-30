<?php
// header('Content-Type: text/html; charset=utf-8');
if(!isset($_FILES["uploadedFile"])) {header('Location: index.html');exit();}
$uploadOk = 1;
// Check file size
if ($_FILES["uploadedFile"]["size"] > 300000) {
	echo "Sorry, your file is too large.<br />";//test1
	$uploadOk = 0;
}

// Allow certain file formats
$fileType = pathinfo($_FILES['uploadedFile']['name'],PATHINFO_EXTENSION);
if($fileType != "srt" ) {
	echo "Sorry, only SRT files are allowed.<br />";//test1
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
$cpsCheck = 25;

$subtitle = new stdClass();
$totalSegmentsOverCps = array();

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
		if($segmentObject->cps > $cpsCheck) array_push($totalSegmentsOverCps, $segmentKey);
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

echo '<h1>Lineas que superaban los 25 CPS: '.count($totalSegmentsOverCps).'</h1>';//op

// Optimization magic begins :)
// Lines over 20 CPS: Fill empty space after and before according to needed time
foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps,$cpsCheck);

// foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps);
// foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps);
// foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps);


	// firstNeighbourLevel(maxVariation)
	// secondNeighbourLevel(maxVariation)
	// thirdNeighbourLevel(maxVariation)



$totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cpsCheck);
echo '<h1>Lineas que superan los 25 CPS después de la optimización: '.count($totalSegmentsOverCps).'</h1>';//op



/************************************************************/
/********************* Subtitle rebuild *********************/
/************************************************************/

foreach ($subtitle as $thisSegmentKey => $segment) {
	// echo $segment->sequence;//ss
	// echo '<br />';//ss
	// echo formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds);//ss
	// echo '<br />';//ss
	// if(isset($segment->textLine1)) echo $segment->textLine1.'<br />';//ss
	// if(isset($segment->textLine2)) echo $segment->textLine2.'<br />';//ss
	// if(isset($segment->textLine3)) echo $segment->textLine3.'<br />';//ss
	// echo '<br />';//ss
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

function updateSequenceData ($subtitle,$sequence) {
	$subtitle->$sequence->sequenceDuration = $subtitle->$sequence->endTimeInMilliseconds - $subtitle->$sequence->startTimeInMilliseconds;
	$subtitle->$sequence->cps = calculateCps($subtitle->$sequence->sequenceDuration,$subtitle->$sequence->totalCharacters);
	// updateSequenceTimes('start',$subtitle,$sequence);
	// updateSequenceTimes('end',$subtitle,$sequence);
}

function updateSequenceDuration ($subtitle,$sequence) {
	$subtitle->$sequence->sequenceDuration = $subtitle->$sequence->endTimeInMilliseconds - $subtitle->$sequence->startTimeInMilliseconds;
}

function updateSequenceCps ($subtitle,$sequence) {
	$subtitle->$sequence->cps = calculateCps($subtitle->$sequence->sequenceDuration,$subtitle->$sequence->totalCharacters);
}

function checkNeededTime ($segment) {
	return floor($segment->totalCharacters*40);
}

function checkMissingTime ($segment,$requiredCps) {
	return floor($segment->totalCharacters*1000/$requiredCps) - $segment->sequenceDuration;
}

function checkAvailableTimeBefore ($subtitle,$sequence) {
	$previousSequence = $sequence - 1;
	return $subtitle->$sequence->startTimeInMilliseconds - $subtitle->$previousSequence->endTimeInMilliseconds;
}

function checkAvailableTimeAfter ($subtitle,$sequence) {
	$nextSequence = $sequence +1;
	return $subtitle->$nextSequence->startTimeInMilliseconds - $subtitle->$sequence->endTimeInMilliseconds;
}

function checkLinesOverCps ($subtitle,$totalSegmentsOverCps,$maxCps) {
	foreach ($totalSegmentsOverCps as $key => $segmentOverCps) {
		if($subtitle->$segmentOverCps->cps <= $maxCps) {
			// echo $totalSegmentsOverCps[$key];
			unset($totalSegmentsOverCps[$key]);
		}
	}
	return $totalSegmentsOverCps;
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







function fillEmptySpace ($subtitle,$thisSequence,$cpsCheck) {
	$previousSequence = $thisSequence - 1;
	$nextSequence = $thisSequence +1;
	$totalAvailableTime = 0;
	$missingTime = checkMissingTime($subtitle->$thisSequence,$cpsCheck);

	if(property_exists($subtitle,$nextSequence)) {
		$availableTimeAfter = checkAvailableTimeAfter($subtitle,$thisSequence);
		$totalAvailableTime += $availableTimeAfter;
	}

	if(property_exists($subtitle,$previousSequence)) {
		$availableTimeBefore = checkAvailableTimeBefore($subtitle,$thisSequence);
		$totalAvailableTime += $availableTimeBefore;
	}

	if($totalAvailableTime<$missingTime) {
		// Ocupo todo el espacio que tengo disponible aunque no alcance
		if(property_exists($subtitle,$nextSequence)) $subtitle->$thisSequence->endTimeInMilliseconds = $subtitle->$nextSequence->startTimeInMilliseconds-1;
		if(property_exists($subtitle,$previousSequence)) $subtitle->$thisSequence->startTimeInMilliseconds = $subtitle->$previousSequence->endTimeInMilliseconds+1;
	} else {
		// Tengo espacio para alcanzar los cps deseados
		if(isset($availableTimeBefore)) {
			if(isset($availableTimeAfter)) {
				if($availableTimeBefore>ceil($missingTime/2)) {
					if($availableTimeAfter>floor($missingTime/2)) {
						$subtitle->$thisSequence->startTimeInMilliseconds -= ceil($missingTime/2);
						$subtitle->$thisSequence->endTimeInMilliseconds += floor($missingTime/2);
					} else {
						$subtitle->$thisSequence->endTimeInMilliseconds = $subtitle->$nextSequence->startTimeInMilliseconds-1;
						updateSequenceDuration($subtitle,$thisSequence);
						$subtitle->$thisSequence->startTimeInMilliseconds -= checkMissingTime($subtitle->$thisSequence,$cpsCheck);
					}
				} else {
					$subtitle->$thisSequence->startTimeInMilliseconds = $subtitle->$previousSequence->endTimeInMilliseconds+1;
					updateSequenceDuration($subtitle,$thisSequence);
					$subtitle->$thisSequence->endTimeInMilliseconds += checkMissingTime($subtitle->$thisSequence,$cpsCheck);
				}
			} else {
				// Last line
			}
		} else {
			// First line
		}
	}
	// Update sequence duration
	updateSequenceData($subtitle,$thisSequence);
	return;
}


// Upcoming fixes: Lines under  second
?>