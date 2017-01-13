<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**************************************************************/
/*
/*                 MÉTODOS DE OPTIMIZACIÓN
/*
/**************************************************************/

function runMethod1 ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration) {
    // (-1|-2|-3|1|2|3) minificado
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    for($level = 1; $level <= 3; $level++) {
        backwardMovement ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration,$level);
        $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    }

    for($level = 1; $level <= 3; $level++) {
        forwardMovement ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration,$level);
        $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    }

    $cps=18;
    $totalSegmentsOverCps = checkAllLinesCps($subtitle,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    // $totalSegmentsUnderMinDuration = checkAllUnderMinDuration($subtitle,$minDuration);
    // print_r($totalSegmentsUnderMinDuration);
    // foreach ($totalSegmentsUnderMisnDuration as $segmentUnderMinDuration) fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
    // $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    // foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
    // $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    return $subtitle;
}

function runMethod2 ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration) {
    // (-1|-2|-3|1|2|3) **** LEGACY ****
    // echo '<h1>Lineas que superaban los 25 CPS: '.count($totalSegmentsOverCps).'</h1>';//op1
    
    // 1)
    // Ocupo espacios vacíos atrás, y luego adelante
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    // 2)
    // Siempre que sea posible incremento los cps de la línea anterior (nivel -1) hasta una de [-1] o [0] alcance los $cps
    // Línea [0] aprovecha el espacio liberado atrás
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $previousSegment = $segmentOverCps - 1;
        if($subtitle->$previousSegment->cps < $cps) {
            if(checkCpsIncreaseGain($subtitle->$previousSegment,$cps,$minDuration) > checkMissingTime($subtitle->$segmentOverCps,$cps)) {
                $reduceTime = checkMissingTime($subtitle->$segmentOverCps,$cps);
            } else {
                $reduceTime = checkCpsIncreaseGain($subtitle->$previousSegment,$cps,$minDuration);
            }
            reduceDuration($subtitle,$segmentOverCps-1,$reduceTime);
            fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
        } else {
            // Línea anterior [-1] supera o iguala los $cps
        }
    }
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    // 3)
    // Siempre que sea posible incremento los cps de la línea anterior (nivel -2) hasta una de [-2] o [-1] alcance los $cps
    // Línea [-1] se mueve hacia atrás
    // Línea [0] aprovecha el espacio liberado atrás
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $previousSegment = $segmentOverCps - 2;
        if($subtitle->$previousSegment->cps < $cps) {
            if(checkCpsIncreaseGain($subtitle->$previousSegment,$cps,$minDuration) > checkMissingTime($subtitle->$segmentOverCps,$cps))  {
                $reduceTime = checkMissingTime($subtitle->$segmentOverCps,$cps);
            } else {
                $reduceTime = checkCpsIncreaseGain($subtitle->$previousSegment,$cps,$minDuration);
            }            
            reduceDuration ($subtitle,$segmentOverCps-2,$reduceTime);
            moveLineBackward($subtitle,$segmentOverCps-1,$reduceTime,$maxVariation,$cps);
            fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
        } else {
            // Linea anterior nivel [-2] supera o iguala los $cps
        }
    }
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    // 4)
    // 
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $previousSegment = $segmentOverCps - 3;
        if($subtitle->$previousSegment->cps < $cps) {
            if(checkCpsIncreaseGain($subtitle->$previousSegment,$cps,$minDuration) > checkMissingTime($subtitle->$segmentOverCps,$cps))  {
                $reduceTime = checkMissingTime($subtitle->$segmentOverCps,$cps);
            } 
            else {
                $reduceTime = checkCpsIncreaseGain($subtitle->$previousSegment,$cps,$minDuration);
            }
            reduceDuration ($subtitle,$segmentOverCps-3,$reduceTime);
            moveLineBackward($subtitle,$segmentOverCps-2,$reduceTime,$maxVariation,$cps);
            moveLineBackward($subtitle,$segmentOverCps-1,$reduceTime,$maxVariation,$cps);
            fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
        } else {
            // Linea anterior nivel [-3] supera o iguala los $cps
        }
    }
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    // 5)
    // 
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $nextSegment = $segmentOverCps + 1;
        if($subtitle->$nextSegment->cps < $cps) {
            if(checkCpsIncreaseGain($subtitle->$nextSegment,$cps,$minDuration) > checkMissingTime($subtitle->$segmentOverCps,$cps)) {
                $reduceTime=checkMissingTime($subtitle->$segmentOverCps,$cps);
            } else {
                $reduceTime=checkCpsIncreaseGain($subtitle->$nextSegment,$cps,$minDuration);
            }
            reduceDuration ($subtitle,$nextSegment,$reduceTime);
            moveLineForward($subtitle,$nextSegment,$reduceTime,$maxVariation,$cps);
            fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
        
        } else {
            // Linea siguiente nivel [1] supera o iguala los $cps
        }
    }
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    // 6)
    //
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $nextSegment = $segmentOverCps + 2;
        if($subtitle->$nextSegment->cps < $cps) {
            if(checkCpsIncreaseGain($subtitle->$nextSegment,$cps,$minDuration) > checkMissingTime($subtitle->$segmentOverCps,$cps)) {
                $reduceTime=checkMissingTime($subtitle->$segmentOverCps,$cps);
            } else {
                $reduceTime=checkCpsIncreaseGain($subtitle->$nextSegment,$cps,$minDuration);
            }
            reduceDuration ($subtitle,$nextSegment,$reduceTime);
            moveLineForward($subtitle,$nextSegment,$reduceTime,$maxVariation,$cps);
            moveLineForward($subtitle,$nextSegment-1,$reduceTime,$maxVariation,$cps);
            fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
        
        } else {
            // Linea siguiente nivel [1] supera o iguala los $cps
        }
    }
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    // 7)
    //
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $nextSegment = $segmentOverCps + 3;
        if($subtitle->$nextSegment->cps < $cps) {
            if(checkCpsIncreaseGain($subtitle->$nextSegment,$cps,$minDuration) > checkMissingTime($subtitle->$segmentOverCps,$cps)) {
                $reduceTime=checkMissingTime($subtitle->$segmentOverCps,$cps);
            } else {
                $reduceTime=checkCpsIncreaseGain($subtitle->$nextSegment,$cps,$minDuration);
            }
            reduceDuration ($subtitle,$nextSegment,$reduceTime);
            moveLineForward($subtitle,$nextSegment,$reduceTime,$maxVariation,$cps);
            moveLineForward($subtitle,$nextSegment-1,$reduceTime,$maxVariation,$cps);
            moveLineForward($subtitle,$nextSegment-2,$reduceTime,$maxVariation,$cps);
            fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
        
        } else {
            // Linea siguiente nivel [1] supera o iguala los $cps
        }
    }

    // Extiendo las que puedo a hasta 18 CPS
    $cps=18;

    // rearmo el array
    $totalSegmentsOverCps=array();
    for($i=0; $i<$totalSequences; $i++) {
        $subtitle->$i->cps = calculateCps($subtitle->$i->sequenceDuration, $subtitle->$i->totalCharacters);
        if($subtitle->$i->cps > $cps) array_push($totalSegmentsOverCps, $i);
    }

    // relleno hacia adelante y hacia atras
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    return $subtitle;
}

function runMethod3 ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration) {
    // (1|-1|2|-2|3|-3) minificado
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    for($level = 1; $level <= 3; $level++) {
        forwardMovement ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration,$level);
        $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
        backwardMovement ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration,$level);
        $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    }

    $cps=18;
    $totalSegmentsOverCps = checkAllLinesCps($subtitle,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

    return $subtitle;
}

function runMethod4 ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration) {
}

?>