<?php 
/**************************************************************/
/*
/*                 FUNCIONES BASE
/*
/**************************************************************/
// printEnhancedSubtitle ($subtitle,$totalSequences)
// downloadEnhancedSubtitle ($subtitle,$totalSequences,$filename)
// calculateMilliseconds ($hour,$minute,$second,$millisecond)
// calculateCps($duration,$characters)
// formatMilliseconds ($milliseconds)
// updateSequenceData ($subtitle,$segment)
// updateSequenceDuration ($subtitle,$segment)
// updateSequenceCps ($subtitle,$segment)
// checkNeededTime ($segment,$cps)
// checkMissingTime ($segment,$cps)
// checkAvailableTimeBefore ($subtitle,$segment)
// checkAvailableTimeAfter ($subtitle,$segment)
// checkLinesOverCps ($subtitle,$totalSegmentsOverCps,$cps)
// checkAllLinesCps ($subtitle,$cps)
// checkAllUnderMinDuration ($subtitle,$minDuration)
// setToLimitCps ($subtitle,$segment,$cps)
// checkCpsIncreaseGain ($segment,$cps,$minDuration)
// reduceDuration ($subtitle,$segment,$milliseconds)
// thisLineOverCps ($subtitle,$segment,$cps)
// fillEmptySpace ($subtitle,$segment,$cps)
// fillEmptySpaceBefore ($subtitle,$segment,$cps)
// fillEmptySpaceAfter ($subtitle,$segment,$cps)
// getSubtitleFromUrl($url)
// moveLineBackward($subtitle,$segment,$milliseconds,$maxVariation,$cps)
// moveLineForward($subtitle,$segment,$milliseconds,$maxVariation,$cps)
                                                                

// Muestra el subtítulo optimizado en pantalla
function printEnhancedSubtitle ($subtitle,$totalSequences) {
    foreach ($subtitle as $thisSegmentKey => $segment) {
        /* Reconstrucción del subtítulo */
        echo $segment->sequence;//ss
        echo '<br />';//ss
        echo formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds);//ss
        echo '<br />';//ss
        if(isset($segment->textLine1)) echo $segment->textLine1.'<br />';//ss
        if(isset($segment->textLine2)) echo $segment->textLine2.'<br />';//ss
        if(isset($segment->textLine3)) echo $segment->textLine3.'<br />';//ss
        echo '<br />';//ss
    }
    echo ($totalSequences+1)."<br />99:99:50,000 --> 99:99:59,999<br />Enhanced with Love in SubAdictos.net<br />";
}

// Muestra el subtítulo optimizado en pantalla
function downloadEnhancedSubtitle ($subtitle,$totalSequences,$filename) {
    $subtitleString = '';
    foreach ($subtitle as $thisSegmentKey => $segment) {
        $sequenceString = $segment->sequence."\r\n";//sf
        $sequenceString .= formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds)."\r\n";//sf
        if(isset($segment->textLine1)) $sequenceString .= utf8_decode($segment->textLine1)."\r\n";//sf
        if(isset($segment->textLine2)) $sequenceString .= utf8_decode($segment->textLine2)."\r\n";//sf
        if(isset($segment->textLine3)) $sequenceString .= utf8_decode($segment->textLine3)."\r\n";//sf
        $sequenceString .= "\r\n";//sf
        $subtitleString .= $sequenceString;//sf
    }
    $subtitleString .= ($totalSequences+1)."\r\n99:99:90,000 --> 99:99:99,999\r\nEnhanced with Love in SubAdictos.net\r\n";


    /* Descarga del subtitítulo optimizado */
    // $filename = 'optimizedSubtitle.srt';//sf
    header("Content-Type: text/plain;charset=windows-1252");//sf
    header('Content-Disposition: attachment; filename="'.$filename.'"');//sf
    header("Content-Length: " . strlen($subtitleString));//sf
    echo $subtitleString;//sf
}

function saveEnhancedSubtitle ($subtitle,$totalSequences,$filename) {
    $subtitleString = '';
    foreach ($subtitle as $thisSegmentKey => $segment) {
        $sequenceString = $segment->sequence."\r\n";//sf
        $sequenceString .= formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds)."\r\n";//sf
        if(isset($segment->textLine1)) $sequenceString .= utf8_decode($segment->textLine1)."\r\n";//sf
        if(isset($segment->textLine2)) $sequenceString .= utf8_decode($segment->textLine2)."\r\n";//sf
        if(isset($segment->textLine3)) $sequenceString .= utf8_decode($segment->textLine3)."\r\n";//sf
        $sequenceString .= "\r\n";//sf
        $subtitleString .= $sequenceString;//sf
    }
    $subtitleString .= ($totalSequences+1)."\r\n99:99:90,000 --> 99:99:99,999\r\nEnhanced with Love in SubAdictos.net\r\n";

    // header("Content-Type: text/plain;charset=windows-1252");//sf
    // header('Content-Disposition: attachment; filename="'.$filename.'"');//sf
    // header("Content-Length: " . strlen($subtitleString));//sf
    // echo $subtitleString;//sf
    $filename = uniqid('subtitle-');
    deleteTemporaryFiles();
    file_put_contents('srt/enhanced/'.$filename.'.srt', $subtitleString);
    return $filename;
}

// Recibe horas, minutos, segundos y milisegundos. Devuelve el tiempo total en milisegundos.
function calculateMilliseconds ($hour,$minute,$second,$millisecond) {
    $totalMilliseconds = $hour*3600000+$minute*60000+$second*1000+$millisecond;
    return $totalMilliseconds;
}

// Recibe duración y cantidad de caracteres. Devuelve cps.
function calculateCps($duration,$characters) {
    $cps = round($characters/($duration/1000),2);
    return $cps;
}

// Recibe un tiempo en milisegundos. Devuelve el tiempo en el formato hh:mm:ss,ms
function formatMilliseconds ($milliseconds) {
    $totalSeconds = $milliseconds/1000;
    $secondsWhole = floor($totalSeconds);
    $secondsFraction = round($totalSeconds - $secondsWhole,3)*1000;

    $hours = floor($secondsWhole / 3600);
    $minutes = floor(($secondsWhole / 60) % 60);
    $seconds = $secondsWhole % 60;

    return sprintf("%02d:%02d:%02d,%03d",$hours,$minutes,$seconds,$secondsFraction);
}

// Recibe el subtítulo y un segmento. Actualiza los datos de duración y cps de dicha secuencia (a partir del tiempo de inicio y fin de la línea). No devuelve nada.
function updateSequenceData ($subtitle,$segment) {
    $subtitle->$segment->sequenceDuration = $subtitle->$segment->endTimeInMilliseconds - $subtitle->$segment->startTimeInMilliseconds;
    $subtitle->$segment->cps = calculateCps($subtitle->$segment->sequenceDuration,$subtitle->$segment->totalCharacters);
    // updateSequenceTimes('start',$subtitle,$segment);
    // updateSequenceTimes('end',$subtitle,$segment);
}

// Recibe el subtítulo y un segmento. Actualiza la duración de dicha secuencia. No devuelve nada.
function updateSequenceDuration ($subtitle,$segment) {
    $subtitle->$segment->sequenceDuration = $subtitle->$segment->endTimeInMilliseconds - $subtitle->$segment->startTimeInMilliseconds;
}

// Recibe el subtítulo y un segmento. Actualiza los cps de dicha secuencia. No devuelve nada.
function updateSequenceCps ($subtitle,$segment) {
    $subtitle->$segment->cps = calculateCps($subtitle->$segment->sequenceDuration,$subtitle->$segment->totalCharacters);
}

// Recibe un segmento y los cps. Retorna la duración total que requiere la linea para cumplir con dichos cps.
function checkNeededTime ($segment,$cps) {
    return floor($segment->totalCharacters*1000/$cps);
}

// Recibe un segmento y los cps. Retorna el tiempo extra que requiere la linea para cumplir con dichos cps.
function checkMissingTime ($segment,$cps) {
    return floor($segment->totalCharacters*1000/$cps) - $segment->sequenceDuration;
}

// Recibe el subtítulo y un segmento. Retorna el tiempo libre disponible antes de dicha secuencia.
function checkAvailableTimeBefore ($subtitle,$segment) {
    $previousSequence = $segment - 1;
    return $subtitle->$segment->startTimeInMilliseconds - $subtitle->$previousSequence->endTimeInMilliseconds;
}

// Recibe el subtítulo y un segmento. Retorna el tiempo libre disponible después de dicha secuencia.
function checkAvailableTimeAfter ($subtitle,$segment) {
    $nextSequence = $segment + 1;
    return $subtitle->$nextSequence->startTimeInMilliseconds - $subtitle->$segment->endTimeInMilliseconds;
}

// Recibe el subtítulo, un array con los segmentos que superan los cps originales y los cps a comprobar ahora (pueden ser los mismos que los originales).
// Devuelve un nuevo array con las líneas que superan actualmente los cps.
function checkLinesOverCps ($subtitle,$totalSegmentsOverCps,$cps) {
    foreach ($totalSegmentsOverCps as $key => $segmentOverCps) {
        if($subtitle->$segmentOverCps->cps <= $cps) {
            // echo '<br>'.$totalSegmentsOverCps[$key];//op
            unset($totalSegmentsOverCps[$key]);
        }
    }
    return $totalSegmentsOverCps;
}

function checkAllLinesCps ($subtitle,$cps) {
    $totalSegmentsOverCps = array();
    foreach ($subtitle as $thisSegmentKey => $segment) if($segment->cps > $cps) array_push($totalSegmentsOverCps, $thisSegmentKey);
    return $totalSegmentsOverCps;
}

function checkAllUnderMinDuration ($subtitle,$minDuration) {
    $totalSegmentsUnderMinDuration = array();
    foreach ($subtitle as $thisSegmentKey => $segment) if($segment->sequenceDuration < $minDuration) array_push($totalSegmentsUnderMinDuration, $thisSegmentKey);
    return $totalSegmentsUnderMinDuration;
}

// IMPORTANTE: Llamar a esta funcion solo cuando la línea supera los X cps
// Recibe el subtítulo, un segmento y los cps. Reduce los cps de dicha línea hasta alcanzar el límite. No devuelve nada.
function setToLimitCps ($subtitle,$segment,$cps) {
    $subtitle->$segment->sequenceDuration = checkNeededTime($subtitle->$segment,$cps);
    $subtitle->$segment->endTimeInMilliseconds = $subtitle->$segment->startTimeInMilliseconds + $subtitle->$segment->sequenceDuration;
    updateSequenceCps($subtitle,$segment);
}

// IMPORTANTE: Llamar a esta funcion solo cuando la línea tiene menos de $cps
// Recibe el subtítulo, un segmento y los cps. Devuelve los milisegundos que se ganarían/liberarían si se le incrementan los cps al máximo ($cps).
function checkCpsIncreaseGain ($segment,$cps,$minDuration) {
    $idealSequenceDuration = checkNeededTime($segment,$cps);
    if ($idealSequenceDuration > $minDuration)
        $requiredSequenceDuration = $segment->sequenceDuration - $idealSequenceDuration;
    else
        $requiredSequenceDuration = $segment->sequenceDuration - $minDuration;
        
    return $requiredSequenceDuration;
    
    /*
    if ($requiredSequenceDuration > $minDuration)
    else
        return $minDuration;
    */
}

// Recibe el subtítulo, un segmento y una cantidad de milisegundos. Reduce la duración de dicha línea en esa cantidad de milisegundos.
function reduceDuration ($subtitle,$segment,$milliseconds) {
    $subtitle->$segment->sequenceDuration -= $milliseconds;
    $subtitle->$segment->endTimeInMilliseconds = $subtitle->$segment->startTimeInMilliseconds + $subtitle->$segment->sequenceDuration;
    updateSequenceCps($subtitle,$segment);
}

// Recibe el subtitulo, un segmento y los cps. Devuelve "true" si supera esos cps o no existe la línea, y "false" si no los supera.
function thisLineOverCps ($subtitle,$segment,$cps) {
    if(property_exists($subtitle,$segment) && $subtitle->$segment->cps >= $cps) return false;
    return true;
}

// Actualiza los datos de tiempo en un segmento. No es necesaria.
// function updateSequenceTimes ($timeType,$subtitle,$sequence) {
//  $timeTypeDuration = $timeType.'TimeInMilliseconds';
//  $hourType = $timeType.'Hour';
//  $minuteType = $timeType.'Minute';
//  $secondType = $timeType.'Second';
//  $millisecondType = $timeType.'Millisecond';

//  $totalSeconds = $subtitle->$sequence->$timeTypeDuration/1000;
//  $secondsWhole = floor($totalSeconds);
//  $secondsFraction = round($totalSeconds - $secondsWhole,3)*1000;

//  $hours = floor($secondsWhole / 3600);
//  $minutes = floor(($secondsWhole / 60) % 60);
//  $seconds = $secondsWhole % 60;

//  $subtitle->$sequence->$hourType = $hours;
//  $subtitle->$sequence->$minuteType = $minutes;
//  $subtitle->$sequence->$secondType = $seconds;
//  $subtitle->$sequence->$millisecondType = $secondsFraction;
// }

// Corre la funcion fillEmptySpaceBefore si la línea anterior no supera los $cps y fillEmptySpaceAfter si fillEmptySpaceBefore no soluciono el problema de cps y la línea posterior no supera los $cps. 
function fillEmptySpace ($subtitle,$segment,$cps) {
    // fillEmptySpaceBefore si es la primer línea o hay una línea anterior pero no supera los $cps
    if(thisLineOverCps($subtitle,$segment-1,$cps)) fillEmptySpaceBefore($subtitle,$segment,$cps);
    // fillEmptySpaceAfter si es la última línea o hay una línea posterior pero no supera los $cps y si la línea sigue superando los $cps
    if(thisLineOverCps($subtitle,$segment,$cps) && thisLineOverCps($subtitle,$segment+1,$cps)) fillEmptySpaceAfter($subtitle,$segment,$cps);
}

// Recibe el subtitulo, un segmento y los cps. Completa el espacio vacío antes de la secuencia. Devuelve la cantidad de milisegundos ganados.
function fillEmptySpaceBefore ($subtitle,$segment,$cps) {
    $previousSegment = $segment - 1;

    $missingTime = checkMissingTime($subtitle->$segment,$cps);

    if(property_exists($subtitle,$previousSegment)) $availableTimeBefore = checkAvailableTimeBefore($subtitle,$segment);
    
    if(isset($availableTimeBefore)) {
        if($availableTimeBefore<$missingTime) {
            // Ocupo todo el espacio que tengo disponible aunque no alcance
            $subtitle->$segment->startTimeInMilliseconds = $subtitle->$previousSegment->endTimeInMilliseconds+1;    
        } else {
            // Tengo espacio para alcanzar los cps deseados
            $subtitle->$segment->startTimeInMilliseconds -= $missingTime;
        }
    } else {
        // Primera línea
        $subtitle->$segment->startTimeInMilliseconds -= $missingTime;
        if($subtitle->$segment->startTimeInMilliseconds<0) $subtitle->$segment->startTimeInMilliseconds = 0;
    }
    // Update sequence duration
    updateSequenceData($subtitle,$segment);
    return $subtitle->$segment->startTimeInMillisecondsOriginal - $subtitle->$segment->startTimeInMilliseconds;
}

// Recibe el subtitulo, un segmento y los cps. Completa el espacio vacío después de la secuencia. Devuelve la cantidad de milisegundos ganados.
function fillEmptySpaceAfter ($subtitle,$segment,$cps) {
    $nextSegment = $segment + 1;
    $missingTime = checkMissingTime($subtitle->$segment,$cps);

    if(property_exists($subtitle,$nextSegment)) $availableTimeAfter = checkAvailableTimeAfter($subtitle,$segment);
    
    if(isset($availableTimeAfter)) {
        if($availableTimeAfter<$missingTime) {
            // Ocupo todo el espacio que tengo disponible aunque no alcance
            $subtitle->$segment->endTimeInMilliseconds = $subtitle->$nextSegment->startTimeInMilliseconds-1;    
        } else {
            // Tengo espacio para alcanzar los cps deseados
            $subtitle->$segment->endTimeInMilliseconds += $missingTime;
        }
    } else {
        // Última línea
        $subtitle->$segment->endTimeInMilliseconds -= $missingTime;
    }
    // Update sequence duration
    updateSequenceData($subtitle,$segment);
    return $subtitle->$segment->endTimeInMilliseconds - $subtitle->$segment->endTimeInMillisecondsOriginal;
}

// Recibe la url de un subtítulo y devuelve el subtítulo en un string.
function getSubtitleFromUrl($url) {
    // $refererUrl = 'https://www.tusubtitulo.com/serie/star-wars-rebels/3/8/2235/';
    // $curlUrl = 'https://www.tusubtitulo.com/updated/5/52632/0';

    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING ,"windows-1252");
    curl_setopt($ch, CURLOPT_REFERER, 'Referer:https://www.tusubtitulo.com/');

    $curlResult = curl_exec($ch);
    if(!$curlResult){
        // ERROR VIEW
        $error = 'Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch);
        die();
    }

    $curlResult = mb_convert_encoding($curlResult, 'utf-8', "windows-1252");
    return $curlResult;
}

// Recibe el subtítulo, un segmento, los cps, la variación máxima permitida y los milisegundos a mover el subtítulo hacia atrás.
// Considera si es primera línea, si el movimiento pisaría la línea anterior o si ya no puede moverse más según $maxVariation. 
function moveLineBackward($subtitle,$segment,$milliseconds,$maxVariation,$cps) {
    $previousSegment = $segment-1;
    $startVariation = $subtitle->$segment->startTimeInMillisecondsOriginal - $subtitle->$segment->startTimeInMilliseconds;
    $availableVariation = $maxVariation - $startVariation;

    if($startVariation < $maxVariation) {
        // El comienzo de la secuencia todavía tiene tiempo para moverse sin superar la variación máxima permitida.
        if($milliseconds < $availableVariation) {
            // El tiempo que se pide de movimiento de línea no supera el tiempo disponible para movimiento.
            if(property_exists($subtitle,$previousSegment)) {
                if(($subtitle->$segment->startTimeInMilliseconds - $milliseconds) <= $subtitle->$previousSegment->endTimeInMilliseconds) {
                    // La variación pedida pisaría el fin de línea anterior.
                    $subtitle->$segment->startTimeInMilliseconds = $subtitle->$previousSegment->endTimeInMilliseconds + 1;
                } else {
                    // Puede hacerse la variación de milisegundos pedida.
                    $subtitle->$segment->startTimeInMilliseconds -= $milliseconds;
                }
            } else {
                // Primera línea
                $subtitle->$segment->startTimeInMilliseconds -= $milliseconds;
                if($subtitle->$segment->startTimeInMilliseconds < 0) $subtitle->$segment->startTimeInMilliseconds = 0;
            }
        } else {
            // El tiempo que se pide de movimiento de línea supera el tiempo disponible para movimiento.
            // Solo varío el tiempo $availableVariation.
            if(property_exists($subtitle,$previousSegment)) {
                if(($subtitle->$segment->startTimeInMilliseconds - $availableVariation) <= $subtitle->$previousSegment->endTimeInMilliseconds) {
                    // La variación disponible pisaría el fin de línea anterior.
                    $subtitle->$segment->startTimeInMilliseconds = $subtitle->$previousSegment->endTimeInMilliseconds + 1;
                } else {
                    // Puede hacerse la variación de milisegundos disponible.
                    $subtitle->$segment->startTimeInMilliseconds -= $availableVariation;
                }
            } else {
                // Primera línea
                $subtitle->$segment->startTimeInMilliseconds -= $availableVariation;
                if($subtitle->$segment->startTimeInMilliseconds < 0) $subtitle->$segment->startTimeInMilliseconds = 0;
            }

        }
        $subtitle->$segment->endTimeInMilliseconds = $subtitle->$segment->startTimeInMilliseconds + $subtitle->$segment->sequenceDuration;
    }
    updateSequenceData($subtitle,$segment);
}


// Recibe el subtítulo, un segmento, los cps, la variación máxima permitida y los milisegundos a mover el subtítulo hacia adelante.
// Considera si es última línea, si el movimiento pisaría la línea posterior o si ya no puede moverse más según $maxVariation. 
function moveLineForward($subtitle,$segment,$milliseconds,$maxVariation,$cps) {
    $nextSegment = $segment+1;
    $startVariation = $subtitle->$segment->startTimeInMilliseconds - $subtitle->$segment->startTimeInMillisecondsOriginal;
    $availableVariation = $maxVariation - $startVariation;

    if($startVariation < $maxVariation) {
        // El comienzo de la secuencia todavía tiene tiempo para moverse sin superar la variación máxima permitida.
        if($milliseconds < $availableVariation) {
            // El tiempo que se pide de movimiento de línea no supera el tiempo disponible para movimiento.
            if(property_exists($subtitle,$nextSegment)) {
                if(($subtitle->$segment->startTimeInMilliseconds + $milliseconds + $subtitle->$segment->sequenceDuration) >= $subtitle->$nextSegment->startTimeInMilliseconds) {
                    // La variación pedida pisaría el fin de línea siguiente.
                    $subtitle->$segment->startTimeInMilliseconds += checkAvailableTimeAfter($subtitle,$segment);
                } else {
                    // Puede hacerse la variación de milisegundos pedida.
                    $subtitle->$segment->startTimeInMilliseconds += $milliseconds;
                }
            } else {
                // Última línea
                $subtitle->$segment->startTimeInMilliseconds += $milliseconds;
            }
        } else {
            // El tiempo que se pide de movimiento de línea supera el tiempo disponible para movimiento.
            // Solo varío el tiempo $availableVariation.
            if(property_exists($subtitle,$nextSegment)) {
                if(($subtitle->$segment->startTimeInMilliseconds + $availableVariation + $subtitle->$segment->sequenceDuration) >= $subtitle->$nextSegment->startTimeInMilliseconds) {
                    // La variación disponible pisaría el principio de línea siguiente.
                    $subtitle->$segment->startTimeInMilliseconds += checkAvailableTimeAfter($subtitle,$segment);
                } else {
                    // Puede hacerse la variación de milisegundos disponible.
                    $subtitle->$segment->startTimeInMilliseconds += $availableVariation;
                }
            } else {
                // Última línea
                $subtitle->$segment->startTimeInMilliseconds += $availableVariation;
            }

        }
        $subtitle->$segment->endTimeInMilliseconds = $subtitle->$segment->startTimeInMilliseconds + $subtitle->$segment->sequenceDuration;
    }
    updateSequenceData($subtitle,$segment);
}

function deleteTemporaryFiles() {
    $files = glob('srt/enhanced/*.srt');
    $now   = time();
    foreach($files as $file){
        if(is_file($file)) {
            if ($now - filemtime($file) >= 60 * 10)
            unlink($file);
        }
    }
}
?>