<?php
/************************************************************/
/** Análisis del archivo subido **/ 
/************************************************************/
// header('Content-Type: text/html; charset=utf-8');
if(!isset($_FILES["uploadedFile"])) {header('Location: index.html');exit();}
$uploadOk = 1;
// Chequeo tamaño del archivo
if ($_FILES["uploadedFile"]["size"] > 300000) {
	echo "Sorry, your file is too large.<br />";
	$uploadOk = 0;
}

// Chequeo el formato del archivo
$fileType = pathinfo($_FILES['uploadedFile']['name'],PATHINFO_EXTENSION);
if($fileType != "srt" ) {
	echo "Sorry, only SRT files are allowed.<br />";
	$uploadOk = 0;
}

// Chequeo si $uploadOk está 0 por alguno de los errores
if ($uploadOk == 0) exit();

$fileContent = file_get_contents($_FILES['uploadedFile']['tmp_name']);
$fileContent = utf8_encode ( $fileContent );
// $fileContent = mb_convert_encoding($fileContent, 'HTML-ENTITIES', "UTF-8");



/************************************************************/
/** Valores iniciales para la optimización **/ 
/************************************************************/
$cps = 25;
$maxVariation = 700;



/************************************************************/
/** Parseo del string a un objeto **/
/************************************************************/
$subtitle = new stdClass();
$totalSegmentsOverCps = array();

// Segmento -> conjunto de 3 lineas {secuencia, tiempo, texto}

foreach(preg_split("/\n\s*\n/s", $fileContent) as $segmentKey => $segment){
	$segmentObject = new stdClass();
	$segmentObject->sequence = $segmentKey+1;
	$segmentArray = array();
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $segment) as $key => $line){
		// Guardo temporalmente cada línea del segmento en un array
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
		if($segmentObject->cps > $cps) array_push($totalSegmentsOverCps, $segmentKey);
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
/** Optimización del objeto subtítulo **/
/************************************************************/
// echo '<h1>Lineas que superaban los 25 CPS: '.count($totalSegmentsOverCps).'</h1>';//op
// Fill empty space after and before according to needed time
foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps,$cps);
$totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
foreach ($totalSegmentsOverCps as $segmentOverCps) firstNeighbourLevel($subtitle,$segmentOverCps,$cps,$maxVariation);
$totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

// foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps);
// foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps);


	// firstNeighbourLevel(maxVariation)
	// secondNeighbourLevel(maxVariation)
	// thirdNeighbourLevel(maxVariation)

// echo '<h1>Lineas que superan los 25 CPS después de la optimización: '.count($totalSegmentsOverCps).'</h1>';//op



/************************************************************/
/** Reconstrucción del subtítulo **/
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
/** Descarga del subtitítulo optimizado **/
/************************************************************/
// print_r($subtitle);













/********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/



/************************************************************/
/************************* Functions ************************/
/************************************************************/
// Recibe horas, minutos, segundos y milisegundos. Devuelve el tiempo total en milisegundos.
function calculateMilliseconds($hour,$minute,$second,$millisecond) {
	$totalMilliseconds = $hour*3600000+$minute*60000+$second*1000+$millisecond;
	return $totalMilliseconds;
}

// Recibe duración y cantidad de caracteres. Devuelve cps.
function calculateCps($duration,$characters) {
	$cps = round($characters/($duration/1000),2);
	return $cps;
}

// Recibe un tiempo en milisegundos. Devuelve el tiempo en el formato hh:mm:ss,ms
function formatMilliseconds($milliseconds){
	$totalSeconds = $milliseconds/1000;
	$secondsWhole = floor($totalSeconds);
	$secondsFraction = round($totalSeconds - $secondsWhole,3)*1000;

	$hours = floor($secondsWhole / 3600);
	$minutes = floor(($secondsWhole / 60) % 60);
	$seconds = $secondsWhole % 60;

	return sprintf("%02d:%02d:%02d,%03d",$hours,$minutes,$seconds,$secondsFraction);
}

// Recibe el subtítulo y una secuencia. Actualiza los datos de duración y cps de dicha secuencia (a partir del tiempo de inicio y fin de la línea). No devuelve nada.
function updateSequenceData ($subtitle,$sequence) {
	$subtitle->$sequence->sequenceDuration = $subtitle->$sequence->endTimeInMilliseconds - $subtitle->$sequence->startTimeInMilliseconds;
	$subtitle->$sequence->cps = calculateCps($subtitle->$sequence->sequenceDuration,$subtitle->$sequence->totalCharacters);
	// updateSequenceTimes('start',$subtitle,$sequence);
	// updateSequenceTimes('end',$subtitle,$sequence);
}

// Recibe el subtítulo y una secuencia. Actualiza la duración de dicha secuencia. No devuelve nada.
function updateSequenceDuration ($subtitle,$sequence) {
	$subtitle->$sequence->sequenceDuration = $subtitle->$sequence->endTimeInMilliseconds - $subtitle->$sequence->startTimeInMilliseconds;
}

// Recibe el subtítulo y una secuencia. Actualiza los cps de dicha secuencia. No devuelve nada.
function updateSequenceCps ($subtitle,$sequence) {
	$subtitle->$sequence->cps = calculateCps($subtitle->$sequence->sequenceDuration,$subtitle->$sequence->totalCharacters);
}

// Recibe un segmento y los cps. Retorna la duración total que requiere la linea para cumplir con dichos cps.
function checkNeededTime ($segment,$cps) {
	return floor($segment->totalCharacters*1000/$cps);
}

// Recibe un segmento y los cps. Retorna el tiempo extra que requiere la linea para cumplir con dichos cps.
function checkMissingTime ($segment,$cps) {
	return floor($segment->totalCharacters*1000/$cps) - $segment->sequenceDuration;
}

// function checkSpareTimeForward ($segment,$requiredCps) {
// 	return $segment->sequenceDuration - floor($segment->totalCharacters*1000/$requiredCps);
// }

// function checkSpareTimeBackward ($segment,$requiredCps) {
// 	return $segment->sequenceDuration - floor($segment->totalCharacters*1000/$requiredCps);
// }

// Recibe el subtítulo y una secuencia. Retorna el tiempo libre disponible antes de dicha secuencia.
function checkAvailableTimeBefore ($subtitle,$sequence) {
	$previousSequence = $sequence - 1;
	return $subtitle->$sequence->startTimeInMilliseconds - $subtitle->$previousSequence->endTimeInMilliseconds;
}

// Recibe el subtítulo y una secuencia. Retorna el tiempo libre disponible después de dicha secuencia.
function checkAvailableTimeAfter ($subtitle,$sequence) {
	$nextSequence = $sequence + 1;
	return $subtitle->$nextSequence->startTimeInMilliseconds - $subtitle->$sequence->endTimeInMilliseconds;
}

// Recibe el subtítulo, un array con los segmentos que superan los cps originales y los cps a comprobar ahora (pueden ser los mismos que los originales).
// Devuelve un nuevo array con las líneas que superan actualmente los cps.
function checkLinesOverCps ($subtitle,$totalSegmentsOverCps,$maxCps) {
	foreach ($totalSegmentsOverCps as $key => $segmentOverCps) {
		if($subtitle->$segmentOverCps->cps <= $maxCps) {
			// echo '<br>'.$totalSegmentsOverCps[$key];//op
			unset($totalSegmentsOverCps[$key]);
		}
	}
	return $totalSegmentsOverCps;
}

// Recibe el subtítulo, una secuencia y los cps. Reduce los cps de dicha línea hasta alcanzar el límite. No devuelve nada.
function reduceToLimitCps ($subtitle,$sequence,$cps) {
	// Se llama a esta funcion solo cuando la linea supera los X cps
	$subtitle->$sequence->sequenceDuration = checkNeededTime($subtitle->$sequence,$cps);
	$subtitle->$sequence->endTimeInMilliseconds = $subtitle->$sequence->startTimeInMilliseconds + $subtitle->$sequence->sequenceDuration;
	updateSequenceCps($subtitle,$sequence);
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



























function fillEmptySpace ($subtitle,$thisSequence,$cps) {
	$previousSequence = $thisSequence - 1;
	$nextSequence = $thisSequence + 1;
	$totalAvailableTime = 0;
	$missingTime = checkMissingTime($subtitle->$thisSequence,$cps);

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
			// Last line
			$subtitle->$thisSequence->startTimeInMilliseconds = $subtitle->$previousSequence->endTimeInMilliseconds+1;
			updateSequenceDuration($subtitle,$thisSequence);
			$subtitle->$thisSequence->endTimeInMilliseconds += checkMissingTime($subtitle->$thisSequence,$cps);
		}
		if(property_exists($subtitle,$previousSequence)) $subtitle->$thisSequence->startTimeInMilliseconds = $subtitle->$previousSequence->endTimeInMilliseconds+1;
		else {
			// First line
			$subtitle->$thisSequence->endTimeInMilliseconds = $subtitle->$nextSequence->startTimeInMilliseconds-1;
			updateSequenceDuration($subtitle,$thisSequence);
			$subtitle->$thisSequence->startTimeInMilliseconds -= checkMissingTime($subtitle->$thisSequence,$cps);
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
						$subtitle->$thisSequence->startTimeInMilliseconds -= checkMissingTime($subtitle->$thisSequence,$cps);
					}
				} else {
					$subtitle->$thisSequence->startTimeInMilliseconds = $subtitle->$previousSequence->endTimeInMilliseconds+1;
					updateSequenceDuration($subtitle,$thisSequence);
					$subtitle->$thisSequence->endTimeInMilliseconds += checkMissingTime($subtitle->$thisSequence,$cps);
				}
			} else {
				// Last line
				$subtitle->$thisSequence->startTimeInMilliseconds -= checkMissingTime($subtitle->$thisSequence,$cps);
			}
		} else {
			// First line
			$subtitle->$thisSequence->endTimeInMilliseconds += checkMissingTime($subtitle->$thisSequence,$cps);
		}
	}
	// Update sequence duration
	updateSequenceData($subtitle,$thisSequence);
	return;
}



function fillEmptySpaceBefore($subtitle,$thisSequence,$cps) {

}

function fillEmptySpaceAfter($subtitle,$thisSequence,$cps) {
	
}

function moveLineBackward() {

}

function moveLineForward() {

}

function changeCps() {

}

function firstNeighbourLevel($subtitle,$thisSequence,$cps,$maxVariation) {
	$previousSequence = $thisSequence - 1;
	$previousSequenceLevel2 = $previousSequence - 1;
	$nextSequence = $thisSequence + 1;
	$nextSequenceLevel2 = $nextSequence + 1;
	$missingTime = checkMissingTime($subtitle->$thisSequence,$cps);

	

	$switch = 0;

	// echo $subtitle->$thisSequence->sequence.':<br>';//t4
	if(property_exists($subtitle,$previousSequence)) {
		$previousSequenceCps = $subtitle->$previousSequence->cps;
		if($previousSequenceCps < $cps) {
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
		if($nextSequenceCps < $cps) {
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






		fillEmptySpace($subtitle,$thisSequence,$cps);
		$missingTime = checkMissingTime($subtitle->$thisSequence,$cps);

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
			fillEmptySpace($subtitle,$thisSequence,$cps);
		}

		if($nextSequenceSpareTimeForward>$missingTime) {
			if($missingTime < $maxVariationAvailableForward) {
				$subtitle->$nextSequence->startTimeInMilliseconds += $missingTime;
				$subtitle->$nextSequence->endTimeInMilliseconds += $missingTime;
			} else {
				$subtitle->$nextSequence->startTimeInMilliseconds += $maxVariationAvailableForward;
				$subtitle->$nextSequence->endTimeInMilliseconds += $maxVariationAvailableForward;
			}
			fillEmptySpace($subtitle,$thisSequence,$cps);
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
		// 	fillEmptySpace($subtitle,$thisSequence,$cps);
		// }
	}

	// maxValue

	updateSequenceData($subtitle,$previousSequence);
	updateSequenceData($subtitle,$nextSequence);
}






function secondNeighbourLevel($subtitle,$thisSequence,$cps,$maxVariation) {
}


// Cambiar la duración siempre que mantenga los cps y dure más de 1 seg

// Upcoming fixes: Lines under  second
?>