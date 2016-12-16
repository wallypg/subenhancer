<?php
if(isset($_POST['subtitleUrl']) && filter_var($_POST['subtitleUrl'], FILTER_VALIDATE_URL)) {
    $subtitleContent = getSubtitleFromUrl($_POST['subtitleUrl']);
} elseif(isset($_FILES["uploadedFile"]) && !empty( $_FILES["uploadedFile"]["name"])) {

    /************************************************************/
    /** Análisis del archivo subido **/ 
    /************************************************************/
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
    $subtitleContent = utf8_encode ( $fileContent );
    // $fileContent = mb_convert_encoding($fileContent, 'HTML-ENTITIES', "UTF-8");
} else {
    echo 'Error en el archivo o URL';
    die();
}

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

foreach(preg_split("/\n\s*\n/s", $subtitleContent) as $segmentKey => $segment){
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
            $segmentObject->startTimeInMillisecondsOriginal = $segmentObject->startTimeInMilliseconds;
            $segmentObject->endTimeInMillisecondsOriginal = $segmentObject->endTimeInMilliseconds;
            $segmentObject->sequenceDurationOriginal = $segmentObject->sequenceDuration;
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

$totalSequences = count((array)$subtitle);

/* PROPIEDADES DE CADA SEGMENTO DEL SUBTÍTULO */
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
// [textLine1] [textLine2] [textLine3]
// [cps]
// [startTimeInMillisecondsOriginal]
// [sequenceDurationOriginal]

$lastLine = $totalSequences-1;
if(md5($subtitle->$lastLine->textLine1) == '4bab2f9ce44d40cf4f268094f76bac69') die('Subtítulo ya optimizado');

// echo '<h1>Lineas que superaban los 25 CPS: '.count($totalSegmentsOverCps).'</h1>';//op


//  ██████╗ ██████╗ ████████╗██╗███╗   ███╗██╗███████╗ █████╗ ████████╗██╗ ██████╗ ███╗   ██╗
// ██╔═══██╗██╔══██╗╚══██╔══╝██║████╗ ████║██║╚══███╔╝██╔══██╗╚══██╔══╝██║██╔═══██╗████╗  ██║
// ██║   ██║██████╔╝   ██║   ██║██╔████╔██║██║  ███╔╝ ███████║   ██║   ██║██║   ██║██╔██╗ ██║
// ██║   ██║██╔═══╝    ██║   ██║██║╚██╔╝██║██║ ███╔╝  ██╔══██║   ██║   ██║██║   ██║██║╚██╗██║
// ╚██████╔╝██║        ██║   ██║██║ ╚═╝ ██║██║███████╗██║  ██║   ██║   ██║╚██████╔╝██║ ╚████║
//  ╚═════╝ ╚═╝        ╚═╝   ╚═╝╚═╝     ╚═╝╚═╝╚══════╝╚═╝  ╚═╝   ╚═╝   ╚═╝ ╚═════╝ ╚═╝  ╚═══╝

/************************************************************/
/** Optimización del objeto subtítulo **/
/************************************************************/

// (Linea excedida "A" de Xcps) -> ¿Cuanto tiempo necesita? Xms
//  ¿Linea anterior "B-" excede Xcps?
//          S Fin
//          N Ocupo espacio anterior a "A" (hasta completar espacio o alcanzar Xcps)
//              ¿A cumple con los Xcps?
//                  S Fin
//                  N (1)
//  ¿Linea siguiente "B+" excede Xcps?
//          S Fin
//          N Ocupo espacio posterior a "A" (hasta completar espacio o alcanzar Xcps)
//              ¿A cumple con los Xcps?
//                  S Fin
//                  N Si "C+" no supera los Xcps -> (4)


// ------------------------------------------------------------------------------------
// (1)  Reduzco CPS de "B-" hasta que "A" alcance los Xcps o "B-" alcance los Xcps
//  ¿"A" cumple con Xcps?
//      S Fin
//      N Muevo "B-" hacia atrás espacio disponible (max 700ms de posicion original)
//        Ocupo espacio liberado detrás de "A"
//        Chequeo
//         S Fin
//         N ¿"B-" se movio >= 700ms?
//          S Fin
//          N ¿"C-" tiene mas de Xcps?
//              S Fin
//              N Si "D-" no supera los Xcps -> (2)

// (1)(2)(3) -> Revisar incrementalmente corrimiento de lineas
// -> Reducir cps, mover atrás
// ------------------------------------------------------------------------------------
// (4)  Reduzco CPS de "B+" hasta que "A" alcance los Xcps o "B+" alcance los Xcps
//  ¿"A" cumple con Xcps?
//      S Fin
//      N Muevo "B+" hacia adelante espacio disponible (max 700ms de posicion original)
//        Ocupo espacio liberado delante de "A"
//        Chequeo
//         S Fin
//         N ¿"B+" se movio >= 700ms?
//          S Fin
//          N ¿"C+" tiene mas de Xcps?
//              S Fin
//              N Si "D+" no supera los Xcps -> (5)

// (4)(5)(6) -> Revisar incrementalmente corrimiento de lineas
// -> Reducir cps, mover adelante



// VER LINEAS QUE SUPERAN $CPS:
// foreach ($totalSegmentsOverCps as $segmentOverCps) echo $segmentOverCps.'<br>';

// Elegir método de optimización (0 para mostrar el subtítulo original)
$method = 1;

switch($method) {
    case 0:
        printEnhancedSubtitleOnScreen($subtitle,$totalSequences);
        break;
    case 1:
        // $subtitle1 = $subtitle; -> se puede crear otra variable para probar varios metodos de optimizacion al mismo tiempo y ver cual funciona mejor
        printEnhancedSubtitleOnScreen(runMethod1($subtitle,$totalSegmentsOverCps,$cps,$maxVariation),$totalSequences);
        break;
    case 2:
        // $subtitle2 = $subtitle;
        printEnhancedSubtitleOnScreen(runMethod2($subtitle,$totalSegmentsOverCps,$cps,$maxVariation),$totalSequences);
        break;
    case 3:
        // $subtitle3 = $subtitle;
        // runMethod3($subtitle3);
        break;
    case 4:
        // $subtitle4 = $subtitle;
        // runMethod4($subtitle4);
        break;
}

// echo '<h1>Lineas que superan los 25 CPS después de la optimización: '.count($totalSegmentsOverCps).'</h1>';//op
// print_r($totalSegmentsOverCps);

// OBJETO:
// print_r($subtitle);
// MOSTRAR EN PANTALLA:
// printEnhancedSubtitleOnScreen($subtitle,$totalSequences);
// DESCARGAR SRT:
// downloadEnhancedSubtitle($subtitle,$totalSequences);
die();


// ███╗   ███╗███████╗████████╗██╗  ██╗ ██████╗ ██████╗ ███████╗
// ████╗ ████║██╔════╝╚══██╔══╝██║  ██║██╔═══██╗██╔══██╗██╔════╝
// ██╔████╔██║█████╗     ██║   ███████║██║   ██║██║  ██║███████╗
// ██║╚██╔╝██║██╔══╝     ██║   ██╔══██║██║   ██║██║  ██║╚════██║
// ██║ ╚═╝ ██║███████╗   ██║   ██║  ██║╚██████╔╝██████╔╝███████║
// ╚═╝     ╚═╝╚══════╝   ╚═╝   ╚═╝  ╚═╝ ╚═════╝ ╚═════╝ ╚══════╝

function runMethod1 ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation) {
    

    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $nextSegment = $segmentOverCps + 1;
        fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
    }

	foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $previousSegment = $segmentOverCps - 1;
        if(checkCpsIncreaseGain($subtitle->$previousSegment,$cps) > checkMissingTime($subtitle->$segmentOverCps,$cps)) {
            // echo 'GAIN: '.checkCpsIncreaseGain($subtitle->$previousSegment,$cps).' - Missing time: '.checkMissingTime($subtitle->$segmentOverCps,$cps).'<br>';
            reduceDuration ($subtitle,$segmentOverCps-1,checkMissingTime($subtitle->$segmentOverCps,$cps));
            fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
        } else {
            reduceDuration ($subtitle,$segmentOverCps-1,checkCpsIncreaseGain($subtitle->$previousSegment,$cps));
            fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
            // fillEmptySpaceBefore($subtitle,$segmentOverCps-1,$cps);
        }
    }
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $previousSegment = $segmentOverCps - 2;
        if(checkCpsIncreaseGain($subtitle->$previousSegment,$cps) > checkMissingTime($subtitle->$segmentOverCps,$cps)) 	{
					$reduceTime=checkMissingTime($subtitle->$segmentOverCps,$cps);
		} 
		else {
			        $reduceTime=checkCpsIncreaseGain($subtitle->$previousSegment,$cps);
		}
		reduceDuration ($subtitle,$segmentOverCps-2,$reduceTime,$cps);
		moveLineBackward($subtitle,$segmentOverCps-1,$reduceTime,$maxVariation,$cps);
		fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
			
    }

    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $previousSegment = $segmentOverCps - 3;
        if(checkCpsIncreaseGain($subtitle->$previousSegment,$cps) > checkMissingTime($subtitle->$segmentOverCps,$cps)) 	{
					$reduceTime=checkMissingTime($subtitle->$segmentOverCps,$cps);
		} 
		else {
			        $reduceTime=checkCpsIncreaseGain($subtitle->$previousSegment,$cps);
		}
		reduceDuration ($subtitle,$segmentOverCps-3,$reduceTime,$cps);
		moveLineBackward($subtitle,$segmentOverCps-2,$reduceTime,$maxVariation,$cps);
		moveLineBackward($subtitle,$segmentOverCps-1,$reduceTime,$maxVariation,$cps);
		fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
			
    }

    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $nextSegment = $segmentOverCps + 1;
        fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
    }
	

    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) {
        $nextSegment = $segmentOverCps + 1;
        if(checkCpsIncreaseGain($subtitle->$nextSegment,$cps) > checkMissingTime($subtitle->$segmentOverCps,$cps)) {
			$reduceTime=checkMissingTime($subtitle->$segmentOverCps,$cps);
        } else {
	        $reduceTime=checkCpsIncreaseGain($subtitle->$previousSegment,$cps);
            reduceDuration ($subtitle,$nextSegment,checkCpsIncreaseGain($subtitle->$nextSegment,$cps));
        }
		reduceDuration ($subtitle,$nextSegment,$reduceTime,$cps);
		moveLineForward($subtitle,$nextSegment,$reduceTime,$maxVariation,$cps);
		fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
		}

	foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpace($subtitle,$segmentOverCps,$cps);
    $totalSegmentsOverCps = checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);

	
	//function moveLineForward($subtitle,$segment,$milliseconds,$maxVariation,$cps) {

    // foreach ($totalSegmentsOverCps as $segmentOverCps) firstNeighbourLevel($subtitle,$segmentOverCps,$cps,$maxVariation);
    return $subtitle;
}

function runMethod2 ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation) {
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceBefore($subtitle,$segmentOverCps,$cps);
    checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    foreach ($totalSegmentsOverCps as $segmentOverCps) fillEmptySpaceAfter($subtitle,$segmentOverCps,$cps);
    checkLinesOverCps($subtitle,$totalSegmentsOverCps,$cps);
    return $subtitle;
}

function runMethod3 ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation) {
    return $subtitle;   
}

function runMethod4 ($subtitle,$totalSegmentsOverCps,$cps,$maxVariation) {
    return $subtitle;
}




// ███████╗██╗   ██╗███╗   ██╗ ██████╗████████╗██╗ ██████╗ ███╗   ██╗███████╗
// ██╔════╝██║   ██║████╗  ██║██╔════╝╚══██╔══╝██║██╔═══██╗████╗  ██║██╔════╝
// █████╗  ██║   ██║██╔██╗ ██║██║        ██║   ██║██║   ██║██╔██╗ ██║███████╗
// ██╔══╝  ██║   ██║██║╚██╗██║██║        ██║   ██║██║   ██║██║╚██╗██║╚════██║
// ██║     ╚██████╔╝██║ ╚████║╚██████╗   ██║   ██║╚██████╔╝██║ ╚████║███████║
// ╚═╝      ╚═════╝ ╚═╝  ╚═══╝ ╚═════╝   ╚═╝   ╚═╝ ╚═════╝ ╚═╝  ╚═══╝╚══════╝

// printEnhancedSubtitleOnScreen ($subtitle,$totalSequences)
// downloadEnhancedSubtitle ($subtitle,$totalSequences)
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
// setToLimitCps ($subtitle,$segment,$cps)
// checkCpsIncreaseGain ($segment,$cps)
// reduceDuration ($subtitle,$segment,$milliseconds)
// thisLineOverCps ($subtitle,$segment,$cps)
// updateSequenceTimes ($timeType,$subtitle,$sequence)
// fillEmptySpace ($subtitle,$segment,$cps)
// fillEmptySpaceBefore ($subtitle,$segment,$cps)
// fillEmptySpaceAfter ($subtitle,$segment,$cps)
// getSubtitleFromUrl($url)
// moveLineBackward($subtitle,$segment,$milliseconds,$maxVariation,$cps)
// moveLineForward($subtitle,$segment,$milliseconds,$maxVariation,$cps)
                                                                          
/************************************************************/
/************************* Functions ************************/
/************************************************************/
// Muestra el subtítulo optimizado en pantalla
function printEnhancedSubtitleOnScreen ($subtitle,$totalSequences) {
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
function downloadEnhancedSubtitle ($subtitle,$totalSequences) {
    $subtitleString = '';
    foreach ($subtitle as $thisSegmentKey => $segment) {
        $sequenceString = $segment->sequence."\r\n";//sf
        $sequenceString .= formatMilliseconds($segment->startTimeInMilliseconds).' --> '.formatMilliseconds($segment->endTimeInMilliseconds)."\r\n";//sf
        if(isset($segment->textLine1)) $sequenceString .= $segment->textLine1."\r\n";//sf
        if(isset($segment->textLine2)) $sequenceString .= $segment->textLine2."\r\n";//sf
        if(isset($segment->textLine3)) $sequenceString .= $segment->textLine3."\r\n";//sf
        $sequenceString .= "\r\n";//sf
        $subtitleString .= $sequenceString;//sf
    }
    $subtitleString .= ($totalSequences+1)."\r\n99:99:90,000 --> 99:99:99,999\r\nEnhanced with Love in SubAdictos.net\r\n";


    /* Descarga del subtitítulo optimizado */
    $filename = 'optimizedSubtitle.srt';//sf
    header("Content-Type: text/plain;charset=utf-8");//sf
    header('Content-Disposition: attachment; filename="'.$filename.'"');//sf
    header("Content-Length: " . strlen($subtitleString));//sf
    echo $subtitleString;//sf
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

// IMPORTANTE: Llamar a esta funcion solo cuando la línea supera los X cps
// Recibe el subtítulo, un segmento y los cps. Reduce los cps de dicha línea hasta alcanzar el límite. No devuelve nada.
function setToLimitCps ($subtitle,$segment,$cps) {
    $subtitle->$segment->sequenceDuration = checkNeededTime($subtitle->$segment,$cps);
    $subtitle->$segment->endTimeInMilliseconds = $subtitle->$segment->startTimeInMilliseconds + $subtitle->$segment->sequenceDuration;
    updateSequenceCps($subtitle,$segment);
}

// IMPORTANTE: Llamar a esta funcion solo cuando la línea tiene menos de $cps
// Recibe el subtítulo, un segmento y los cps. Devuelve los milisegundos que se ganarían/liberarían si se le incrementan los cps al máximo ($cps).
function checkCpsIncreaseGain ($segment,$cps) {
    $idealSequenceDuration = checkNeededTime($segment,$cps);
    return $segment->sequenceDuration - $idealSequenceDuration;
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
      die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
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


// Cambiar la duración siempre que mantenga los cps y dure más de 1 seg
// Arreglar líneas de menos de 1 segundo

// Próximamente en index:
// Campo opcional para la URL de la página del subtítulo con el cual precarga campos de datos.
// Datos para el nombre del archivo y créditos.
// Datos guardados en json y sugeridos con select.
// Serie
// # Temporada
// # Capítulo
// Nombre capítulo
// HDTV / DVDRIP / WEBDL
// XVID/x264
// LOL-FLEET lo que sea
// Correctores
// Traducción Original
// Drag and drop del archivo de subtítulo.
?>