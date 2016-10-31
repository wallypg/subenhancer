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
$maxVariation = 700;

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

// echo '<h1>Lineas que superaban los 25 CPS: '.count($totalSegmentsOverCps).'</h1>';//op

// Optimization magic begins :)

// Fill empty space after and before according to needed time
foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps,$cpsCheck);
$totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cpsCheck);
foreach ($totalSegmentsOverCps as $segmentOverCps) firstNeighbourLevel($subtitle,$segmentOverCps,$cpsCheck,$maxVariation);
$totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cpsCheck);

// foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps);
// foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps);


	// firstNeighbourLevel(maxVariation)
	// secondNeighbourLevel(maxVariation)
	// thirdNeighbourLevel(maxVariation)



// echo '<h1>Lineas que superan los 25 CPS después de la optimización: '.count($totalSegmentsOverCps).'</h1>';//op



/************************************************************/
/********************* Subtitle rebuild *********************/
/************************************************************/

foreach ($subtitle as $thisSegmentKey => $segment) {
	echo $segment->sequence;//ss
	echo '<br />';//ss
	echo formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds);//ss
	echo '<br />';//ss
	if(isset($segment->textLine1)) echo $segment->textLine1.'<br />';//ss
	if(isset($segment->textLine2)) echo $segment->textLine2.'<br />';//ss
	if(isset($segment->textLine3)) echo $segment->textLine3.'<br />';//ss
	echo '<br />';//ss
}

/************************************************************/
/******************** Subtitle download *********************/
/************************************************************/


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

// function checkSpareTimeForward ($segment,$requiredCps) {
// 	return $segment->sequenceDuration - floor($segment->totalCharacters*1000/$requiredCps);
// }

// function checkSpareTimeBackward ($segment,$requiredCps) {
// 	return $segment->sequenceDuration - floor($segment->totalCharacters*1000/$requiredCps);
// }

function checkAvailableTimeBefore ($subtitle,$sequence) {
	$previousSequence = $sequence - 1;
	return $subtitle->$sequence->startTimeInMilliseconds - $subtitle->$previousSequence->endTimeInMilliseconds;
}

function checkAvailableTimeAfter ($subtitle,$sequence) {
	$nextSequence = $sequence + 1;
	return $subtitle->$nextSequence->startTimeInMilliseconds - $subtitle->$sequence->endTimeInMilliseconds;
}

function checkLinesOverCps ($subtitle,$totalSegmentsOverCps,$maxCps) {
	foreach ($totalSegmentsOverCps as $key => $segmentOverCps) {
		if($subtitle->$segmentOverCps->cps <= $maxCps) {
			// echo '<br>'.$totalSegmentsOverCps[$key];//op
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
	$nextSequence = $thisSequence + 1;
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
		else {
			// First line
			$subtitle->$thisSequence->startTimeInMilliseconds = $subtitle->$previousSequence->endTimeInMilliseconds+1;
			updateSequenceDuration($subtitle,$thisSequence);
			$subtitle->$thisSequence->endTimeInMilliseconds += checkMissingTime($subtitle->$thisSequence,$cpsCheck);
		}
		if(property_exists($subtitle,$previousSequence)) $subtitle->$thisSequence->startTimeInMilliseconds = $subtitle->$previousSequence->endTimeInMilliseconds+1;
		else {
			// Last line
			$subtitle->$thisSequence->endTimeInMilliseconds = $subtitle->$nextSequence->startTimeInMilliseconds-1;
			updateSequenceDuration($subtitle,$thisSequence);
			$subtitle->$thisSequence->startTimeInMilliseconds -= checkMissingTime($subtitle->$thisSequence,$cpsCheck);
			if($subtitle->$thisSequence->startTimeInMilliseconds<0) $subtitle->$thisSequence->startTimeInMilliseconds = 0;
		}
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
				$subtitle->$thisSequence->startTimeInMilliseconds -= checkMissingTime($subtitle->$thisSequence,$cpsCheck);
			}
		} else {
			// First line
			$subtitle->$thisSequence->endTimeInMilliseconds += checkMissingTime($subtitle->$thisSequence,$cpsCheck);
		}
	}
	// Update sequence duration
	updateSequenceData($subtitle,$thisSequence);
	return;
}



function firstNeighbourLevel($subtitle,$thisSequence,$cpsCheck,$maxVariation) {
	$previousSequence = $thisSequence - 1;
	$previousSequenceLevel2 = $previousSequence - 1;
	$nextSequence = $thisSequence + 1;
	$nextSequenceLevel2 = $nextSequence + 1;
	$missingTime = checkMissingTime($subtitle->$thisSequence,$cpsCheck);

	

	$switch = 0;

	// echo $subtitle->$thisSequence->sequence.':<br>';//t4
	if(property_exists($subtitle,$previousSequence)) {
		$previousSequenceCps = $subtitle->$previousSequence->cps;
		if($previousSequenceCps < $cpsCheck) {
			if(property_exists($subtitle,$previousSequenceLevel2)) {
				$previousSequenceSpareTimeBackward = $subtitle->$previousSequence->startTimeInMilliseconds - $subtitle->$previousSequenceLevel2->endTimeInMilliseconds - 1;
				// echo 'Previous spare: '.$previousSequenceSpareTimeBackward.'<br>';//t4
				$switch++;

			} else {
				// only one level previous
			}
		} else {
			// previous level over cps limit
		}
	} else {
		// first line
	}

	if(property_exists($subtitle,$nextSequence)) {
		$nextSequenceCps = $subtitle->$nextSequence->cps;
		if($nextSequenceCps < $cpsCheck) {
			if(property_exists($subtitle,$nextSequenceLevel2)) {
				$nextSequenceSpareTimeForward = $subtitle->$nextSequenceLevel2->startTimeInMilliseconds - $subtitle->$nextSequence->endTimeInMilliseconds - 1;
				// echo 'Next spare: '.$nextSequenceSpareTimeForward.'<br>';//t4
				$switch++;
			} else {
				// only one level next
			}
		} else {
			// next level over cps limit
		}
	} else {
		// last line
	}

	$missingTimePreviousHalf = ceil($missingTime/2);
	$missingTimeNextHalf = floor($missingTime/2);


	// Primer movimiento tentativo: Movimiento de las lineas anterior y posterior si existen ambas, tienen menos de 25 cps y espacio disponible del otro lado
	if($switch==2) {
		$maxVariationAvailableForward = $maxVariation;
		$maxVariationAvailableBackward = $maxVariation;
		$totalSpareTime = $previousSequenceSpareTimeBackward + $nextSequenceSpareTimeForward;

		if($previousSequenceSpareTimeBackward > $missingTimePreviousHalf) {
			if($missingTimePreviousHalf < $maxVariation) {
				$subtitle->$previousSequence->startTimeInMilliseconds -= $missingTimePreviousHalf;
				$subtitle->$previousSequence->endTimeInMilliseconds -= $missingTimePreviousHalf;
				$maxVariationAvailableBackward -= $missingTimePreviousHalf;
			} else {
				// hay mas espacio para correrlo hacia atrás pero requiere mover la linea mas de la variacion permitida (700ms) - espacio se llena parcialmente
			}
		} else {
			$backwardMovementMilliseconds = $subtitle->$previousSequence->startTimeInMilliseconds - $subtitle->$previousSequenceLevel2->endTimeInMilliseconds;
			if($backwardMovementMilliseconds < $maxVariation) {
				$subtitle->$previousSequence->startTimeInMilliseconds -= $backwardMovementMilliseconds;
				$subtitle->$previousSequence->endTimeInMilliseconds -= $backwardMovementMilliseconds;
			} else {
				// hay mas espacio para correrlo hacia atrás pero requiere mover la linea mas de la variacion permitida (700ms) - espacio destinado a llenarse pero no va a alcanzar
			}
		}

		if($nextSequenceSpareTimeForward > $missingTimeNextHalf) {
			if($missingTimeNextHalf < $maxVariation) {
				$subtitle->$nextSequence->startTimeInMilliseconds += $missingTimeNextHalf;
				$subtitle->$nextSequence->endTimeInMilliseconds += $missingTimeNextHalf;
				$maxVariationAvailableForward -= $missingTimeNextHalf;
			} else {
				// hay mas espacio para correrlo hacia adelante pero requiere mover la linea mas de la variacion permitida (700ms) - espacio se llena parcialmente
			}
		} else {
			$forwardMovementMilliseconds = $subtitle->$nextSequenceLevel2->startTimeInMilliseconds - $subtitle->$nextSequence->endTimeInMilliseconds;
			if($forwardMovementMilliseconds < $maxVariation) {
				$subtitle->$nextSequence->startTimeInMilliseconds += $forwardMovementMilliseconds;
				$subtitle->$nextSequence->endTimeInMilliseconds += $forwardMovementMilliseconds;
			} else {
				// hay mas espacio para correrlo hacia adelante pero requiere mover la linea mas de la variacion permitida (700ms) - espacio destinado a llenarse pero no va a alcanzar
			}
		}

	// } elseif() {

	// } elseif() {






		fillEmptySpace($subtitle,$thisSequence,$cpsCheck);
		$missingTime = checkMissingTime($subtitle->$thisSequence,$cpsCheck);

		// si queda lugar de ambos lados y no alcanza??????
		// recalculate spare time
		$previousSequenceSpareTimeBackward = $subtitle->$previousSequence->startTimeInMilliseconds - $subtitle->$previousSequenceLevel2->endTimeInMilliseconds - 1;
		$nextSequenceSpareTimeForward = $subtitle->$nextSequenceLevel2->startTimeInMilliseconds - $subtitle->$nextSequence->endTimeInMilliseconds - 1;
		
		if($previousSequenceSpareTimeBackward>$missingTime) {
			if($missingTime < $maxVariationAvailableBackward) {
				$subtitle->$previousSequence->startTimeInMilliseconds -= $missingTime;
				$subtitle->$previousSequence->endTimeInMilliseconds -= $missingTime;
			} else {
				$subtitle->$previousSequence->startTimeInMilliseconds -= $maxVariationAvailableBackward;
				$subtitle->$previousSequence->endTimeInMilliseconds -= $maxVariationAvailableBackward;
			}
			fillEmptySpace($subtitle,$thisSequence,$cpsCheck);
		}

		if($nextSequenceSpareTimeForward>$missingTime) {
			if($missingTime < $maxVariationAvailableForward) {
				$subtitle->$nextSequence->startTimeInMilliseconds += $missingTime;
				$subtitle->$nextSequence->endTimeInMilliseconds += $missingTime;
			} else {
				$subtitle->$nextSequence->startTimeInMilliseconds += $maxVariationAvailableForward;
				$subtitle->$nextSequence->endTimeInMilliseconds += $maxVariationAvailableForward;
			}
			fillEmptySpace($subtitle,$thisSequence,$cpsCheck);
		}

		

		// if($totalSpareTime>$missingTime && $missingTime < $maxVariation) {
			
		// 	$previousSequenceSpareTimeBackward = $subtitle->$previousSequence->startTimeInMilliseconds - $subtitle->$previousSequenceLevel2->endTimeInMilliseconds - 1;
		// 	if($previousSequenceSpareTimeBackward>0) {
		// 		$subtitle->$previousSequence->startTimeInMilliseconds -= $missingTime;
		// 		$subtitle->$previousSequence->endTimeInMilliseconds -= $missingTime;
		// 	} else {
		// 		$subtitle->$nextSequence->startTimeInMilliseconds += $missingTime;
		// 		$subtitle->$nextSequence->endTimeInMilliseconds += $missingTime;
		// 	}
		// 	fillEmptySpace($subtitle,$thisSequence,$cpsCheck);
		// }
	}

	// maxValue

	updateSequenceData($subtitle,$previousSequence);
	updateSequenceData($subtitle,$nextSequence);
}

function secondNeighbourLevel($subtitle,$thisSequence,$cpsCheck,$maxVariation) {
}


// Cambiar la duración siempre que mantenga los cps y dure más de 1 seg

// Upcoming fixes: Lines under  second
?>