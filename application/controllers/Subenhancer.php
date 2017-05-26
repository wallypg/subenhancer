<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subenhancer extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->library('session');

		if(!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']) redirect(base_url().'?goto='.urlencode($this->uri->uri_string));

		if($_SESSION['user'] != 'subadictos') redirect(base_url());

		$this->load->library('folder');
		$this->folder->setFolder('subenhancer');


	}


	public function index() {

    	$data = array();

		$getArray = $this->input->get();
		if( isset($getArray['dbg']) ) {
			$data['dbg'] = true;
			$this->load->library('debug');
			$this->debug->ocrDebugger($getArray);
		}
		
		if (file_exists('json/data.json')){
  			$data['dataArray'] = json_decode(file_get_contents('json/data.json'), true);
		}

		if(file_exists('version.log')) {
			$f = fopen('version.log', 'r');
			$line = fgets($f);
			$versionArray = explode('-', $line);
			$data['version'] = 'v'.substr($versionArray[0],-2).$versionArray[1].$versionArray[2].'-'.$versionArray[3];
			fclose($f);
		}

		$this->folder->view('index',$data);
	}


	public function editor ($jsonFile='data') {
		$data = array();
		if (file_exists('json/'.$jsonFile.'.json')) $data['json'] = file_get_contents('json/'.$jsonFile.'.json');
		$data['jsonFile'] = $jsonFile;
		if ($jsonFile == 'log') $data['log'] = true;
		$this->folder->view('editor',$data);	
	}


	public function download() {
		$getArray = $this->input->get();
		if(isset($getArray['file']) && isset($getArray['name'])) {
			$filename = $getArray['name'];
			$path = 'srt/enhanced/'.$getArray['file'].'.srt';
			if($filename != '' && file_exists($path)) {
				$fileContent = file_get_contents($path);
				unlink($path);
				header("Content-Type: text/plain;charset=windows-1252");
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header("Content-Length: " . strlen($fileContent));
				echo $fileContent;
				die();
			}
		}
		header('Location:'.base_url());
		die();
	}


	// AJAX request para guardar cambios en el editor de JSON
	public function save($jsonFile) {

		if($this->input->is_ajax_request()) {
			$postArray = $this->input->post();

			if( isset($postArray['updatedJson']) ) {
				$json = strip_tags($postArray['updatedJson']);
				$jsonString = json_decode($json);
				
				if($jsonFile == 'data') {
					$categories = array('tv_show', 'codec', 'format', 'quality', 'rip_group', 'other', 'task', 'subtitler');
					$countCategories = count((array)$jsonString);
					$propertiesExists = true;

					for ($i = 0; $propertiesExists == true && $i < 8; $i++) {
						$propertiesExists = (property_exists($jsonString, $categories[$i])) ? true : false;
					}
				}

				if(json_last_error() == JSON_ERROR_NONE) {
					if( ($jsonFile == 'data' && $propertiesExists && $countCategories == 8) || $jsonFile == 'log' ) {
						$jsonRecoded = json_encode($jsonString,JSON_PRETTY_PRINT);
				 		file_put_contents("json/".$jsonFile.".json",$jsonRecoded);
				 		echo $jsonRecoded;
					}
				}
			}
		}

	}


	// AJAX request para datos del subtítulo
	public function subtitleData() {

		if( $this->input->is_ajax_request() ) {
			
			// $postArray['info_url'] = 'https://www.tusubtitulo.com/serie/the-walking-dead/7/7/750/';
			// $postArray['sub_url'] = 'https://www.tusubtitulo.com/updated/5/52771/0';

			$postArray = $this->input->post();

			$validUrlPatternInfo = '#^https://www.tusubtitulo.com/serie/[^/]+/[0-9]+/[0-9]+/[0-9]+/$#';
			$validUrlPatternInfo2 = '#^https://www.tusubtitulo.com/episodes/[0-9]+/[^/]+(/)?$#';
			$validUrlPatternSub = '#^https://www.tusubtitulo.com/[^/]+/[0-9]+/[0-9]+(/[0-9]+)?$#';
			
			if(isset($postArray['info_url']) && (preg_match($validUrlPatternInfo, $postArray['info_url']) || preg_match($validUrlPatternInfo2, $postArray['info_url']) )) {
				$infoUrl = $postArray['info_url'];
				if(isset($postArray['sub_url']) && preg_match($validUrlPatternSub, $postArray['sub_url'])) 
					$subUrl = str_replace('https://www.tusubtitulo.com/', '', $postArray['sub_url']);
			} else die ('URL INVALIDA');
			
			// CURL
			// $curlResource=curl_init();
			// curl_setopt_array($curlResource, array(
			//   CURLOPT_RETURNTRANSFER => 1,
			//   CURLOPT_URL => $infoUrl
			// ));
			
			// $curlResult = curl_exec($curlResource);
			
			$this->load->library('ua');
						
			$ch = curl_init();
			
			// $url = 'https://'.$proxyServer.'.hidemyass.com/includes/process.php?action=update&u='.urlencode($postArray['info_url']); //option 1
			// $proxyServer = rand(1,7);

			$userAgent = $this->ua->randomUserAgent();
			$url = 'https://ssl-proxy.my-addr.org/myaddrproxy.php/'.$postArray['info_url'];

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

			if(!($curlResult = curl_exec($ch))) {
			  die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
			}
			curl_close($ch);

			$this->load->helper('domxpath');

			// Objeto DOM
			$dom = new domDocument;
			libxml_use_internal_errors(true);
			$dom->loadHTML($curlResult); 
			$dom->preserveWhiteSpace = false;
			$finder = new DomXPath($dom);

			$titleId = 'cabecera-subtitulo';
			$episodeId = 'ssheader';

			$titleString = trim(getElementById($finder,$titleId)->nodeValue);
			$rawEpisodeString = trim(getElementById($finder,$episodeId)->nodeValue);

			preg_match('/[0-9]{2}\:[0-9]{2}\:[0-9]{2}(?=\s*$)/', $rawEpisodeString, $episodeLength);
			$rawEpisodeString = str_replace($episodeLength[0],'',$rawEpisodeString);
			$episodeString = trim($rawEpisodeString);

			preg_match('/(?<=Temporada )[0-9]+(?=,)/', $episodeString, $seasonNumber);
			preg_match('/(?<=Capítulo )[0-9]+/', $episodeString, $episodeNumber);

			$tvShow = strstr($episodeString, ',', true);
			$episodeTitle = substr(strstr($titleString, ' - '), 3);
			$episodeLength = $episodeLength[0];
			$seasonNumber = $seasonNumber[0];
			$episodeNumber = $episodeNumber[0];

			$data = array('tv_show'=>$tvShow, 'season'=>$seasonNumber, 'episode_number'=>$episodeNumber, 'episode_title'=>$episodeTitle, 'duration'=>$episodeLength);
			
			if(isset($subUrl)) {
				$nodes = getElementByClass($finder, 'ssdiv');
				for ($i = $nodes->length - 1; $i > -1; $i--) {
					$versionNode = $nodes->item($i);
					$arrayVersions[] = $versionNode->ownerDocument->saveHTML($versionNode);
				}
				// print_r($arrayVersions);
				foreach($arrayVersions as $version)
					if(strpos($version, $subUrl) !== false) {
						preg_match('/(?<=Versión ).+(?=,)/', $version, $group);
						$data['group'] = $group[0];
					}
			}

			echo json_encode($data);
		}
	}


	/***************************************************/
	/***************************************************/
	/*
	/*
	/*					ENHANCE
	/*
	/*
	/***************************************************/ 
	/***************************************************/ 

	public function enhance() {
		// print_r($_POST);
		// die();

		$this->load->helper('core');
		$this->load->library('ocr');
		$this->load->library('uds');

		// MÉTODO DE OPTIMIZACIÓN
		$method = 1;

		$postArray = $this->input->post();

		// MODO "DEBUG"
		if(isset($postArray['dbg'])) $method = 4;

		$error = array('error'=>true);

		if(isset($postArray['sub_url']) && !empty($postArray['sub_url'])) {
		    $validUrlPatternSub = '#^https://www.tusubtitulo.com/[^/]+/[0-9]+/[0-9]+(/[0-9]+)?$#';
		    if(preg_match($validUrlPatternSub, $postArray['sub_url'])) $subtitleContent = getSubtitleFromUrl($postArray['sub_url']);
		    elseif(preg_match('/^https?:\/\/.+\.srt$/', $postArray['sub_url'])) $subtitleContent = getSrtSubtitle($postArray['sub_url']);
		    else $subtitleContent = getInternalSubtitle($postArray['sub_url']);
		} elseif(isset($_FILES["uploaded_file"]) && !empty( $_FILES["uploaded_file"]["name"])) {

		    /************************************************************/
		    /** Análisis del archivo subido **/ 
		    /************************************************************/
		    $uploadOk = 1;
		    // Chequeo tamaño del archivo
		    if (($_FILES["uploaded_file"]["size"] > 300000 && !isset($postArray['dbg'])) || ($_FILES["uploaded_file"]["size"] > 800000 && isset($postArray['dbg'])))  {
		        $errorMessage = "Peso de archivo demasiado grande.";
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
		} elseif(isset($postArray['srtContent'])) {
		    $error['streamSize'] = strlen ($postArray['srtContent']);
		    if($error['streamSize'] > 500000) die(json_encode($error));
		    $subtitleContent = $postArray['srtContent'];
		} else {
		    // ERROR
		    $error['subtitleSource'] = 'Error en el archivo o URL';
		    die(json_encode($error));
		}


		/************************************************************/
		/** Chequeo si ya fue optimizado **/ 
		/************************************************************/

		if(strpos($subtitleContent, "Enhanced with Love in SubAdictos.net") !== false) {
		    // ERROR
		    $error['alreadyEnhanced'] = 'Subtítulo ya optimizado';
		    die(json_encode($error));
		}


		/************************************************************/
		/** Valores iniciales para la optimización **/ 
		/************************************************************/
		$cps = 25;
		$maxVariation = 800;
		$minDuration = 900;


		/************************************************************/
		/** Opciones **/ 
		/************************************************************/

		// OCRs: ocr-asia-team, ocr-reduce, ocr-reduce-at
		$ocrString = '';
		// if(isset($postArray['ocr'])) {
		// 	foreach($postArray['ocr'] as $ocr) {
		// 		if($ocr=='ocr-reduce') $ocrString .= 'a';
		// 		if($ocr=='ocr-asia-team') $ocrString .= 'b';
		// 		if($ocr=='ocr-reduce-at') $ocrString .= 'c';
		// 		if($ocr=='ocr-unidades-sentido') $ocrString .= 'd';
		// 	}
		// }
		// $ocrString = 'ddd';//testing

		// Unión líneas cortas
		// Líneas duplicadas
		// Unidades de sentido
		// Puntos suspensivos
		// Mantener nombre de archivo
		// Capítulo final de temporada / película? (Créditos)

		/************************************************************/
		/** Parseo del string a un objeto **/
		/************************************************************/
		$subtitle = new stdClass();
		$totalSegmentsOverCps = array();

		// Segmento -> conjunto de 3 lineas {secuencia, tiempo, texto}
		$ocrCorrections = array();
		$ocrCounter = 0;

		foreach(preg_split("/\n\s*\n/s", $subtitleContent) as $segmentKey => $segment){

		    $segmentObject = new stdClass();
		    // ->sequence
		    $segmentObject->sequence = $segmentKey+1;

		    $segmentArray = array();
		    foreach(preg_split("/((\r?\n)|(\r\n?))/", $segment) as $key => $line){
		        // Guardo temporalmente cada línea del segmento en un array
		        $segmentArray[$key] = $line;
		        if(preg_match('/\d{2}:\d{2}:\d{2},\d{3} --> \d{2}:\d{2}:\d{2},\d{3}/',$line)) {
		            sscanf($line, "%d:%d:%d,%d --> %d:%d:%d,%d",$startHour,$startMinute,$startSecond,$startMillisecond,$endHour,$endMinute,$endSecond,$endMillisecond);

		            // ->[times]
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



			// $segmentArray contiene secuencia entera: [0]->Nº Sec, [1]->Tiempos, [2][3]...->Líneas
		    $segmentObject->totalCharacters = 0;
		    // for($i=2; $i<count($segmentArray)-1; $i++) {//dual
		        // $textLine = 'textLine'.($i-1);//dual
			// print_r($segmentArray);
			// echo "\n\n";

		    	$nl = 0;//oneliner
		    	$textLine = '';//oneliner
		    	for($i=2; $i<=count($segmentArray)-1; $i++) //oneliner
		    		if(!empty($segmentArray[$i]))
			    		if(!$textLine)//oneliner
			    			$textLine = $segmentArray[$i];//oneliner
			    		else//oneliner
			    		{//oneliner
			    			$nl++;//oneliner
			    			$textLine .= "\n".$segmentArray[$i];//oneliner
			    		}//oneliner


		        if(!empty($ocrString)) {
		            // $ocrCheckArray = $this->ocr->ocrCheck($segmentArray[$i],$ocrString);//dual
		            $ocrCheckArray = $this->ocr->ocrCheck($textLine,$ocrString);//oneliner

		            if(!empty($ocrCheckArray)) {
		            	$ocrCounter += $ocrCheckArray['ocrCounter'];
		                if(!isset($ocrCorrections[$segmentObject->sequence])) $ocrCorrections[$segmentObject->sequence] = array();
		                
		                // $segmentObject->$textLine = $ocrCheckArray['ocredLine'];//dual
		                $segmentObject->textLine = $ocrCheckArray['ocredLine'];//oneliner
		                array_push($ocrCorrections[$segmentObject->sequence], array('found'=>$ocrCheckArray['found'],'replaced'=>$ocrCheckArray['replaced']));
		                // $ocrCorrections[$segmentObject->sequence][$ocrCheckArray['found']] = $ocrCheckArray['replaced'];
		            } else {
		                // $segmentObject->$textLine = $segmentArray[$i];//dual
		                $segmentObject->textLine = $textLine;//oneliner
		            }

		        // } else $segmentObject->$textLine = $segmentArray[$i];//dual
		        } else $segmentObject->textLine = $textLine;//oneliner


		        /**********/
		        // UDS
		        // $uds = 1;
		        // if($uds) $segmentObject->textLine = $this->uds->fixUds($segmentObject->textLine);
		        // print_r($segmentObject->textLine);
		        // die();
		        /**********/

		        // $segmentObject->totalCharacters += mb_strlen($segmentObject->$textLine);//dual
		        $segmentObject->totalCharacters = mb_strlen($segmentObject->textLine)-$nl;//oneliner
		    // }//dual

		    // print_r($segmentObject); die();

		    if(isset($segmentObject->sequenceDuration) && isset($segmentObject->totalCharacters)) {
		        $segmentObject->cps = calculateCps($segmentObject->sequenceDuration, $segmentObject->totalCharacters);
		        if($segmentObject->cps > $cps) array_push($totalSegmentsOverCps, $segmentKey);
		    }
		    if($segmentObject->totalCharacters>0) $subtitle->$segmentKey = $segmentObject;
		}

		/************************************************************/

		// argumentos a ocrCheck
		// revisar md5
		// revisar cantidad de caracteres con \n

		// print_r($subtitle);s

		/************************************************************/
		/** Chequeo integridad del objeto **/
		/************************************************************/
		$objectCorruption = 0;


		$arrayCastedSubtitle = (array)$subtitle;
		if(empty($arrayCastedSubtitle)) {
		    $error['emptyObject'] = 'El parseo del subtítulo devolvió un objeto vacío.';
		    $objectCorruption = 1;
		} else {
		    for($objectCorruption = 0, $i = 0; $objectCorruption == 0 && $i < count($arrayCastedSubtitle); $i++ ) {
		    	// print_r($subtitle);
		        if(!isset($subtitle->$i)) {
		            $error['missingSegment'] = 'No se encuentra la secuencia '.($i+1);
		            $objectCorruption = 1;
		        } elseif($i+1 != $subtitle->$i->sequence) {
		        	$error['sequenceDivergence'] = 'Desfasaje en la secuencia '.($i+1);
		            $objectCorruption = 1;
		        } else {
		            $elementCount = count((array)$subtitle->$i);
		            if ( $elementCount != 18 ) {
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

		// echo '<br>'.$ocrCounter.'<br>';

		$totalSequences = count($arrayCastedSubtitle);
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


		/************************************************************/
		/**     Guardado de datos en un JSON      **/
		/** y construcción del nombre del archivo **/
		/************************************************************/
		if (file_exists('json/log.json'))
			$logArray = json_decode(file_get_contents('json/log.json'), true);
		else
			$logArray = array();

	
		if(isset($postArray['keepName'])) {
			$filename = $postArray['keepName'];
		} else {

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
			                        'task' => array(),
			                        'subtitler' => array(),
			                        'enhanced' => array()
			                    );

			$filename = '';

			if(isset($postArray['tv_show']) && !empty($postArray['tv_show'])) {
			    $filename = trim($postArray['tv_show']);
			    if(!in_array($postArray['tv_show'], $dataArray['tv_show'])) {
			    	array_push($dataArray['tv_show'], $postArray['tv_show']);
			    	sort($dataArray['tv_show'], SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
			    }
			}
			if(isset($postArray['season']) && is_numeric($postArray['season'])) {
			    $filename .= '.S'.str_pad($postArray['season'], 2, "0", STR_PAD_LEFT);
			}
			if(isset($postArray['episode_number']) && !empty($postArray['episode_number']) && is_numeric($postArray['episode_number'])) {
			    $filename .= 'E'.str_pad($postArray['episode_number'], 2, "0", STR_PAD_LEFT);
			} else $postArray['episode_number'] = '';
			if(isset($postArray['episode_title']) && !empty($postArray['episode_title'])) {
			    $filename .= '.'.trim($postArray['episode_title']);
			}
			if(isset($postArray['other']) && !empty($postArray['other'])) {
			    $filename .= '.'.trim($postArray['other']);
			    if(!in_array($postArray['other'], $dataArray['other'])) {
			    	array_push($dataArray['other'], $postArray['other']);
			    	sort($dataArray['other'], SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
			    }
			}
			if(isset($postArray['quality']) && !empty($postArray['quality'])) {
			    $filename .= '.'.trim($postArray['quality']);
			    if(!in_array($postArray['quality'], $dataArray['quality'])) {
			    	array_push($dataArray['quality'], $postArray['quality']);
			    	sort($dataArray['quality'], SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
			    }
			}
			if(isset($postArray['format']) && !empty($postArray['format'])) {
			    $filename .= '.'.trim($postArray['format']);
			    if(!in_array($postArray['format'], $dataArray['format'])) {
			    	array_push($dataArray['format'], $postArray['format']);
			    	sort($dataArray['format'], SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
			    }
			}
			if(isset($postArray['codec']) && !empty($postArray['codec'])) {
			    $filename .= '.'.trim($postArray['codec']);
			    if(!in_array($postArray['codec'], $dataArray['codec'])) {
			    	array_push($dataArray['codec'], $postArray['codec']);
			    	sort($dataArray['codec'], SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
			    }
			}
			if(isset($postArray['rip_group']) && !empty($postArray['rip_group'])) {
			    $filename .= '-'.trim($postArray['rip_group']);
			    if(!in_array($postArray['rip_group'], $dataArray['rip_group'])) {
			    	array_push($dataArray['rip_group'], $postArray['rip_group']);
			    	sort($dataArray['rip_group'], SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
			    }
			}
			if(isset($postArray['task']) && !empty($postArray['task'])) {
			    if(!in_array($postArray['task'], $dataArray['task'])) {
			    	array_push($dataArray['task'], $postArray['task']);
			    	sort($dataArray['task'], SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
			    }
			}
			if(isset($postArray['subtitler']) && !empty($postArray['subtitler'])) {
			    if(!in_array($postArray['subtitler'], $dataArray['subtitler'])) {
			    	array_push($dataArray['subtitler'], $postArray['subtitler']);
			    	sort($dataArray['subtitler'], SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
			    }
			} else $postArray['subtitler'] = '';

			if($filename == '') $filename .= 'enhancedSubtitle';
			$filename .= '.srt';

			$filename = preg_replace('/\s+/', '.', $filename);
			$notAllowed = array_merge(
			                array_map('chr', range(0,31)),
			                array("<", ">", ":", '"', "/", "\\", "|", "?", "*"));
			$filename = str_replace($notAllowed, ".", $filename);
			$filename = preg_replace('/\.+/', '.', $filename);

			file_put_contents("json/data.json",json_encode($dataArray,JSON_PRETTY_PRINT));

		}

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
		$this->load->helper('enhance');

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

		$threadMessage = "[CENTER][IMG]http://imagenes.subadictos.net/novedad/SubsDisponibles.gif[/IMG]\n\n[B][SIZE=4]Capítulo: [COLOR=\"#FF0000\"]".$postArray['episode_number']."[/COLOR].[/SIZE][/B]\n\n[SIZE=3]Agradecimientos a:\nTraducción: [B][/B]";
		// $threadMessage = "[CENTER][IMG]http://imagenes.subadictos.net/novedad/SubsDisponibles.gif[/IMG]\n\n[B][SIZE=4]Capítulo: [COLOR=\"#FF0000\"]".$postArray['episode_number']."[/COLOR].[/SIZE][/B]\n\n[SIZE=3]Agradecimientos a:\nTraducción: [B]".$postArray['translation']."[/B]";
		// revisar

		// revisar
		if(isset($postArray['editor']) && !empty($postArray['editor'])) {
		    $threadMessage .= "\nCorrección: [B]".$postArray['editor']."[/B]";
		}
		$threadMessage .= "[/SIZE][/CENTER]";

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



		// LOG
		$logThis = new stdClass();
		$logThis->timestamp = date('Y-m-d H:i:s');
		$logThis->filename = $filename;
		$logThis->tempFilename = $tempFilename;
		$logThis->enhanced = $enhancedLines . ' of ' . $originalLinesOverCps;
		$logThis->efficiency = ($originalLinesOverCps) ? round($enhancedLines*100/$originalLinesOverCps,1) . '%' : '0%';
		array_push($logArray, $logThis);
		file_put_contents("json/log.json",json_encode($logArray,JSON_PRETTY_PRINT));



		echo json_encode($data);
		die();		

	}

	public function bug() {
		$this->folder->view('bugs');
	}

	public function ocrEditor() {
		$this->folder->view('ocrEditor');
	}
}