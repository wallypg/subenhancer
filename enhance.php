<?php
require('functions.php');
require('ocr.php');

// MÉTODO DE OPTIMIZACIÓN
$method = 1;
// MODO "DEBUG"
if(isset($_POST['dbg'])) $method = 4;  

$error = array('error'=>true);

if(isset($_POST['sub_url']) && !empty($_POST['sub_url'])) {
    $validUrlPatternSub = '#^https://www.tusubtitulo.com/[^/]+/[0-9]+/[0-9]+(/[0-9]+)?$#';
    if(preg_match($validUrlPatternSub, $_POST['sub_url'])) $subtitleContent = getSubtitleFromUrl($_POST['sub_url']);
    elseif(preg_match('/^https?:\/\/.+\.srt$/', $_POST['sub_url'])) $subtitleContent = getSrtSubtitle($_POST['sub_url']);
    else $subtitleContent = getInternalSubtitle($_POST['sub_url']);
} elseif(isset($_FILES["uploaded_file"]) && !empty( $_FILES["uploaded_file"]["name"])) {

    /************************************************************/
    /** Análisis del archivo subido **/ 
    /************************************************************/
    $uploadOk = 1;
    // Chequeo tamaño del archivo
    if ($_FILES["uploaded_file"]["size"] > 300000) {
        $errorMessage = "Peso de archivoo demasiado grande.";
        $uploadOk = 0;
    }

    // Chequeo el formato del archivo
    $fileType = pathinfo($_FILES['uploaded_file']['name'],PATHINFO_EXTENSION);
    if($fileType != "srt" ) {
        $errorMessage = "Solo están permitidos archivos de extensión '.SRT'.";
        $uploadOk = 0;
    }

    // Chequeo si $uploadOk está 0 por alguno de los errores
    if ($uploadOk == 0) {
        // ERROR
        $error['fileUpload'] = $errorMessage;
        die(json_encode($error));
    }

    $fileContent = file_get_contents($_FILES['uploaded_file']['tmp_name']);
    $subtitleContent = utf8_encode ( $fileContent );
    // $fileContent = mb_convert_encoding($fileContent, 'HTML-ENTITIES', "UTF-8");
} elseif(isset($_POST['srtContent'])) {
    $error['streamSize'] = strlen ($_POST['srtContent']);
    if($error['streamSize'] > 500000) die(json_encode($error));
    $subtitleContent = $_POST['srtContent'];
} else {
    // ERROR
    $error['subtitleSource'] = 'Error en el archivo o URL';
    die(json_encode($error));
}


/************************************************************/
/** Valores iniciales para la optimización **/ 
/************************************************************/
$cps = 25;
$maxVariation = 700;
$minDuration = 900;

/************************************************************/
/** Parseo del string a un objeto **/
/************************************************************/
$subtitle = new stdClass();
$totalSegmentsOverCps = array();

// Segmento -> conjunto de 3 lineas {secuencia, tiempo, texto}
$ocrCorrections = array();

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

        if(isset($_POST['ocr']) && $_POST['ocr'] == 'true') {
            $ocrCheckArray = ocrCheck($segmentArray[$i]);
            // print_r($ocrCheckArray);
            // die();
            if(!empty($ocrCheckArray)) {
                if(!isset($ocrCorrections[$segmentObject->sequence])) $ocrCorrections[$segmentObject->sequence] = array();
                
                $segmentObject->$textLine = $ocrCheckArray['ocredLine'];
                array_push($ocrCorrections[$segmentObject->sequence], array('found'=>$ocrCheckArray['found'],'replaced'=>$ocrCheckArray['replaced']));
                // $ocrCorrections[$segmentObject->sequence][$ocrCheckArray['found']] = $ocrCheckArray['replaced'];
            } else {
                $segmentObject->$textLine = $segmentArray[$i];
            }

        } else $segmentObject->$textLine = $segmentArray[$i];

        $segmentObject->totalCharacters += mb_strlen($segmentArray[$i]);
    }
    
    if(isset($segmentObject->sequenceDuration) && isset($segmentObject->totalCharacters)) {
        $segmentObject->cps = calculateCps($segmentObject->sequenceDuration, $segmentObject->totalCharacters);
        if($segmentObject->cps > $cps) array_push($totalSegmentsOverCps, $segmentKey);
    }
    if($segmentObject->totalCharacters>0) $subtitle->$segmentKey = $segmentObject;
}


// CHEQUEO INTEGRIDAD DEL OBJETO
$objectCorruption = 0;

if(empty((array)$subtitle)) {
    $error['emptyObject'] = 'El parseo del subtítulo devolvió un objeto vacío.';
    $objectCorruption = 1;
} else {
    for($objectCorruption = 0, $i = 1; $objectCorruption == 0 && $i < count((array)$subtitle); $i++ ) {
        if(!isset($subtitle->$i)) {
            $error['missingSegment'] = 'No se encuentra la secuencia '.($i+1);
            $objectCorruption = 1;
        } else {
            $elementCount = count((array)$subtitle->$i);
            if ( $elementCount < 18 || $elementCount >20 ) {
                $error['missingProperties'] = 'Propiedades faltantes en la secuencia '.($i+1);
                $objectCorruption = 1;
            }
        }
    }
}
// ERROR
if($objectCorruption) die(json_encode($error));    
    

// OCR FEEDBACK
$ocrTable = '<table class="table table-striped ocr-table">';
foreach ($ocrCorrections as $sequenceKey => $sequenceValue) {
    $ocrTable .= '<tr><td colspan="2">'.$sequenceKey.'</td></tr>';
    foreach($sequenceValue as $ocrCorrectedLine) {
        $ocrTable .= '<tr>
                        <td>'.$ocrCorrectedLine['found'].'</td>
                        <td>'.$ocrCorrectedLine['replaced'].'</td>
                      </tr>';
    }
}
$ocrTable .= '</table>';

$totalSequences = count((array)$subtitle);
$originalLinesOverCps = count($totalSegmentsOverCps);
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
if(md5($subtitle->$lastLine->textLine1) == '4bab2f9ce44d40cf4f268094f76bac69') {
    // ERROR
    $error['alreadyEnhanced'] = 'Subtítulo ya optimizado';
    die(json_encode($error));
}



/************************************************************/
/**     Guardado de datos en un JSON      **/
/** y construcción del nombre del archivo **/
/************************************************************/

if (file_exists('json/data.json'))
  $dataArray = json_decode(file_get_contents('json/data.json'), true);
 else
  $dataArray = array(
                        'tv_show' => array(),
                        'codec' => array(),
                        'format' => array(),
                        'quality' => array(),
                        'rip_group' => array(),
                        'other' => array(),
                        'editor' => array(),
                        'translation' => array(),
                        'enhanced' => array()
                    );

$filename = '';

if(isset($_POST['tv_show']) && !empty($_POST['tv_show'])) {
    $filename = trim($_POST['tv_show']);
    if(!in_array($_POST['tv_show'], $dataArray['tv_show'])) array_push($dataArray['tv_show'], $_POST['tv_show']);
}
if(isset($_POST['season']) && is_numeric($_POST['season'])) {
    $filename .= '.S'.str_pad($_POST['season'], 2, "0", STR_PAD_LEFT);
}
if(isset($_POST['episode_number']) && !empty($_POST['episode_number']) && is_numeric($_POST['episode_number'])) {
    $filename .= 'E'.str_pad($_POST['episode_number'], 2, "0", STR_PAD_LEFT);
} else $_POST['episode_number'] = '';
if(isset($_POST['episode_title']) && !empty($_POST['episode_title'])) {
    $filename .= '.'.trim($_POST['episode_title']);
}
if(isset($_POST['other']) && !empty($_POST['other'])) {
    $filename .= '.'.trim($_POST['other']);
    if(!in_array($_POST['other'], $dataArray['other'])) array_push($dataArray['other'], $_POST['other']);
}
if(isset($_POST['quality']) && !empty($_POST['quality'])) {
    $filename .= '.'.trim($_POST['quality']);
    if(!in_array($_POST['quality'], $dataArray['quality'])) array_push($dataArray['quality'], $_POST['quality']);
}
if(isset($_POST['format']) && !empty($_POST['format'])) {
    $filename .= '.'.trim($_POST['format']);
    if(!in_array($_POST['format'], $dataArray['format'])) array_push($dataArray['format'], $_POST['format']);
}
if(isset($_POST['codec']) && !empty($_POST['codec'])) {
    $filename .= '.'.trim($_POST['codec']);
    if(!in_array($_POST['codec'], $dataArray['codec'])) array_push($dataArray['codec'], $_POST['codec']);
}
if(isset($_POST['rip_group']) && !empty($_POST['rip_group'])) {
    $filename .= '-'.trim($_POST['rip_group']);
    if(!in_array($_POST['rip_group'], $dataArray['rip_group'])) array_push($dataArray['rip_group'], $_POST['rip_group']);
}
if(isset($_POST['editor']) && !empty($_POST['editor'])) {
    if(!in_array($_POST['editor'], $dataArray['editor'])) array_push($dataArray['editor'], $_POST['editor']);
}
if(isset($_POST['translation']) && !empty($_POST['translation'])) {
    if(!in_array($_POST['translation'], $dataArray['translation'])) array_push($dataArray['translation'], $_POST['translation']);
} else $_POST['translation'] = '';

if($filename == '') $filename .= 'enhancedSubtitle';
$filename .= '.srt';

$filename = preg_replace('/\s+/', '.', $filename);
$notAllowed = array_merge(
                array_map('chr', range(0,31)),
                array("<", ">", ":", '"', "/", "\\", "|", "?", "*"));
$filename = str_replace($notAllowed, ".", $filename);
$filename = preg_replace('/\.+/', '.', $filename);

array_push($dataArray['enhanced'], $filename);
file_put_contents("json/data.json",json_encode($dataArray,JSON_PRETTY_PRINT));



/**************************************************************/
/*
/*                        "MAIN"
/*
/**************************************************************/

// OBJETO:
// print_r($subtitle);
// die();


// Elegir método de optimización (0 para mostrar el subtítulo original)
// ^^^^ DECLARADO ARRIBA ^^^^

// MOSTRAR EN PANTALLA: printEnhancedSubtitle($subtitle,$totalSequences);
// DESCARGAR SRT: downloadEnhancedSubtitle($subtitle,$totalSequences,$filename);
switch($method) {
    case 0:
        // Sub original
        printEnhancedSubtitle($subtitle,$totalSequences);
        break;
    case 1:
        // (-1|-2|-3|1|2|3) minificado
        $tempFilename = saveEnhancedSubtitle(runMethod1($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration),$totalSequences,$filename);
        break;
    case 2:
        // (-1|-2|-3|1|2|3) **** LEGACY ****
        downloadEnhancedSubtitle(runMethod2($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration),$totalSequences,$filename);
        break;
    case 3:
        // (1|-1|2|-2|3|-3) minificado
        downloadEnhancedSubtitle(runMethod3($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration),$totalSequences,$filename);
        break;
    case 4:   
        printEnhancedSubtitle(runMethod1($subtitle,$totalSegmentsOverCps,$cps,$maxVariation,$minDuration),$totalSequences,$filename);
        die();
        break;
}

$afterEnhancementLinesOverCps = count(checkAllLinesCps($subtitle,25));
$enhancedLines = $originalLinesOverCps-$afterEnhancementLinesOverCps;

$threadMessage = '[CENTER][IMG]http://imagenes.subadictos.net/novedad/SubsDisponibles.gif[/IMG]

[B][SIZE=4]Capítulo: [COLOR="#FF0000"]'.$_POST['episode_number'].'[/COLOR].[/SIZE][/B]
[SIZE=3]
Agradecimientos a:

Traducción: [B]'.$_POST['translation'].'[/B]';
if(isset($_POST['editor']) && !empty($_POST['editor'])) {
    $threadMessage .= 'Corrección [B][COLOR="#800080"]'.$_POST['editor'].'[/COLOR][/B]';
}
$threadMessage .= '[/SIZE][/CENTER]';

$efficiencyMessage = ($originalLinesOverCps) ? round($enhancedLines*100/$originalLinesOverCps,1).'% de eficiencia en la optimización.' : '¡No había líneas que optimizar!';
$enhancementMessage = $enhancedLines.' líneas mejoradas de '.$originalLinesOverCps.' que superaban los 25 CPS.';

// header('Location: index.php')
$data = array(
        'filename' => $filename,
        'tempFilename' => $tempFilename,
        'threadMessage' => $threadMessage,
        'efficiencyMessage' => $efficiencyMessage,
        'enhancementMessage' => $enhancementMessage,
        'ocrCorrections' => $ocrTable
        );
echo json_encode($data);
die();

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


/**************************************************************/
/*
/*                 FUNCIONES DE OPTIMIZACIÓN
/*
/**************************************************************/
function backwardMovement ($subtitle,$arrayOfSegments,$cps,$maxVariation,$minDuration,$level) {
    // Siempre que sea posible incremento los cps de la línea -$level hasta dicha línea o la siguiente alcance los $cps límite
    // Luego la/s línea/s se mueve/n hacia atrás hasta que la original ocupa el espacio liberado hacia atrás
    foreach ($arrayOfSegments as $thisSegment) {
        // $previousSegment no es necesariamente el anterior a $thisSegment
        $previousSegment = $thisSegment - $level;
        if(property_exists($subtitle,$previousSegment)) {
            if($subtitle->$previousSegment->cps < $cps) {
                if(checkCpsIncreaseGain($subtitle->$previousSegment,$cps,$minDuration) > checkMissingTime($subtitle->$thisSegment,$cps))  {
                    $reduceTime = checkMissingTime($subtitle->$thisSegment,$cps);
                } else {
                    $reduceTime = checkCpsIncreaseGain($subtitle->$previousSegment,$cps,$minDuration);
                }
                reduceDuration ($subtitle,$thisSegment-$level,$reduceTime);
                if($level >= 2) moveLineBackward($subtitle,$thisSegment-$level+1,$reduceTime,$maxVariation,$cps);
                if($level == 3) moveLineBackward($subtitle,$thisSegment-1,$reduceTime,$maxVariation,$cps);
                fillEmptySpaceBefore($subtitle,$thisSegment,$cps);
            } else {
                // Linea anterior nivel [-$level] supera o iguala los $cps
            }
        }
    }
    return $subtitle;
}

function forwardMovement ($subtitle,$arrayOfSegments,$cps,$maxVariation,$minDuration,$level) {
    foreach ($arrayOfSegments as $thisSegment) {
        // $nextSegment no es necesariamente el siguiente a $thisSegment
        $nextSegment = $thisSegment + $level;
        if(property_exists($subtitle,$nextSegment)) {
            if($subtitle->$nextSegment->cps < $cps) {
                if(checkCpsIncreaseGain($subtitle->$nextSegment,$cps,$minDuration) > checkMissingTime($subtitle->$thisSegment,$cps)) {
                    $reduceTime=checkMissingTime($subtitle->$thisSegment,$cps);
                } else {
                    $reduceTime=checkCpsIncreaseGain($subtitle->$nextSegment,$cps,$minDuration);
                }
                reduceDuration ($subtitle,$nextSegment,$reduceTime);
                if($level == 3) moveLineForward($subtitle,$thisSegment+3,$reduceTime,$maxVariation,$cps);
                if($level >= 2) moveLineForward($subtitle,$thisSegment+2,$reduceTime,$maxVariation,$cps);
                moveLineForward($subtitle,$thisSegment+1,$reduceTime,$maxVariation,$cps);
                fillEmptySpaceAfter($subtitle,$thisSegment,$cps);        
            } else {
                // Linea siguiente nivel [$level] supera o iguala los $cps
            }
        }
    }
    return $subtitle;
}

?>