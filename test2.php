<?php 
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