<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subshuffle extends CI_Controller {

	var $defaultMessages;
	var $subtitlesPendings;
	var $skipped;

	function __construct() {
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');

		$this->load->library('folder');
		$this->folder->setFolder('subshuffle');

		$forbiddenUsers = array('subadictos', 'subextractor');
		if(!$this->session->userdata('isLoggedIn')) redirect(base_url().'?goto='.urlencode($this->uri->uri_string));
		elseif(in_array($this->session->userdata('user'), $forbiddenUsers)) redirect(base_url());

		$this->load->model('wikiadictos');

		$this->defaultMessages = [
			"empty" => 'Nada para traducir...<br />¡por ahora!<br /><i class="fa fa-smile-o"></i>',
			"noMore" => "¡Eso es todo, amigos!",
			"noSequence" => "Estamos trabajando para solucionar los incovenientes... ¡recuerda que es la versión beta! Mientras tanto... <a class='chill' href='http://www.urbandictionary.com/define.php?term=subadictos+and+chill' target='_blank'>subadictos and chill</a> ;)."
			// "noSequence" => "¿Ninguna secuencia para traducir?<br />Dudoso, probablemente sea un error. <br />Por favor reportarlo en la sección \"Reporte de bugs\".<br /> ¡Muchas gracias!"
			// "noSequence" => "¿Ninguna secuencia para traducir?<br />Mmmm, dudoso. Probablemente sea un error. Por favor copiar el mensaje de abajo y enviarlo en la sección de \"Reporte de bugs\". ¡Muchas gracias!"
		];

		// Liberar secuencias tomadas hace más de 3 horas
		$this->wikiadictos->freeLockedSequences();

		$this->subtitlesPendings = $this->wikiadictos->getSubtitlesAndPendingSequences();
	}

	public function index() {

		$subtitles = $this->wikiadictos->getSubtitles();
		
		if(empty($subtitles)) $subtitles = $this->defaultMessages;
		$data['subtitles'] = $subtitles;
		$data['firstLogIn'] = $this->session->userdata('firstLogIn');

		// $this->session->set_userdata( array('savedCurrent' => true) );
		$this->folder->view('index',$data);

		// 	if($tokenized) {
		// 		$data['sequenceInfo'] = $randomSequence;
		// 		$this->folder->view('index',$data);
		// 	} else {
		// 		$message = "Error locking random sequence.";
		// 		show_error($message, $status_code = 60, $heading = 'An Error Was Encountered');
		// 	}

		// } else {
		// 	$message = "Error retrieving random sequence.";
		// 	show_error($message, $status_code = 60, $heading = 'An Error Was Encountered');
		// }
	}

	public function checkedConsiderations() {
		$this->session->set_userdata( array('firstLogIn' => false ) );
	}

	public function reportBug() {
		$return = true;
		if( $this->input->post('report') ) $this->wikiadictos->reportBug($this->session->userdata('userId'), $this->input->post('report'));
		else $return = false;
		echo $return;
	}

	public function log() {
		// $this->folder->view('test');
	}

	public function myTranslations($loadMore = null) {
		if(!is_null($loadMore) && !is_numeric($loadMore)) die('Error de argumento.');

		$myTranslations = $this->wikiadictos->userTranslations($this->session->userdata('userId'), $loadMore);		
		if(empty($myTranslations)) $myTranslations = $this->defaultMessages;
		echo json_encode($myTranslations);
	}

	public function subtitles() {
		$subtitles = $this->wikiadictos->getSubtitles();
		if(empty($subtitles)) $subtitles = $this->defaultMessages;
		echo json_encode($subtitles);
	}

	public function subtitleSequences($subId = null, $loadMore = null) {
		if( !is_numeric($subId) || (!is_null($loadMore) && !is_numeric($loadMore)) ) die('Error de argumento.');

		$subtitleSequences = $this->wikiadictos->getSubtitleSequences($subId, $loadMore);
		if(empty($subtitleSequences)) $subtitleSequences = $this->defaultMessages;
		echo json_encode($subtitleSequences);
	}

	public function randomSequence() {
		$randomSub = rand(0, count($this->subtitlesPendings)-1);
				
		$randomSequence = $this->wikiadictos->getRandomSequence($this->subtitlesPendings[$randomSub]->subId, rand(1,$this->subtitlesPendings[$randomSub]->sequences));

		// var_dump($randomSequence);
		// die();

		if(empty($randomSequence)) $randomSequence = $this->defaultMessages;
		else {
			$randomSequence->title = $this->subtitlesPendings[$randomSub]->title;
			$randomSequence->translated = false;
			$randomSequence->taken = null;
			$randomSequence->version = -1;
			$randomSequence->hasNext = ($this->wikiadictos->checkSequenceExistence($randomSequence->subID,$randomSequence->sequence+1)) ? 1 : 0;
			$randomSequence->hasPrev = ($this->wikiadictos->checkSequenceExistence($randomSequence->subID,$randomSequence->sequence-1)) ? 1 : 0;
		
			// print_r($randomSequence);
			// die();
			$this->session->set_userdata(array( 'skipped' => $this->session->userdata('skipped') . ',' . $randomSequence->entryID));
			if( is_null($this->session->userdata('taken')) ) {
				$this->wikiadictos->untokenizeSequence($this->session->userdata('subId'), $this->session->userdata('sequence'));
			}


			$this->session->set_userdata( array(
				'translated' => $randomSequence->translated,
				'version' => $randomSequence->version,
				'taken' => $randomSequence->taken,
				'subId' => $randomSequence->subID,
				'sequence' => $randomSequence->sequence,
				'start_time' => $randomSequence->start_time,
				'start_time_fraction' => $randomSequence->start_time_fraction,
				'end_time' => $randomSequence->end_time,
				'end_time_fraction' => $randomSequence->end_time_fraction
			) );

			$this->wikiadictos->tokenizeSequence($randomSequence->subID, $randomSequence->sequence, $this->session->userdata('userId'));
		}
		echo json_encode($randomSequence);
	}
	
	public function getSequence($subId = null, $sequence = null) {
		if( !is_numeric($subId) || !is_numeric($sequence)) die('Argumentos incorrectos.');

		$sequenceObj = $this->wikiadictos->getSequence($subId,$sequence);		

		if(empty($sequenceObj)) $sequenceObj = $this->defaultMessages;
		else {
			$sequenceObj->title = $this->wikiadictos->getFileName($sequenceObj->subID)->title;
			$sequenceObj->hasNext = ($this->wikiadictos->checkSequenceExistence($sequenceObj->subID,$sequenceObj->sequence+1)) ? 1 : 0;
			$sequenceObj->hasPrev = ($this->wikiadictos->checkSequenceExistence($sequenceObj->subID,$sequenceObj->sequence-1)) ? 1 : 0;

			// $sequenceObj->usertoken = ( $checkSequence != null ) ? $checkSequence : null;
			$this->session->set_userdata(array('skipped' => $this->session->userdata('skipped') . ',' . $sequenceObj->entryID));
			if( is_null($this->session->userdata('taken')) ) {
				$this->wikiadictos->untokenizeSequence($this->session->userdata('subId'), $this->session->userdata('sequence'));
			}
			$checkSequence = $this->wikiadictos->checkSequenceToken($subId,$sequence,$this->session->userdata('userId'));
			
			$sequenceObj->translated = false;
			$sequenceObj->taken = null;
			$sequenceObj->version = -1;
			if( is_null($checkSequence) ) {
				// translated
				$sequenceObj->translated = true;
				$translatedText = $this->wikiadictos->getTranslatedSequence($subId,$sequence);
				// var_dump($translatedText);
				if(!empty($translatedText)) {

					$sequenceObj->text_es = $translatedText->text;
					$sequenceObj->version = $translatedText->version;
				}
			} else {
				if($checkSequence<0) {
					// free
					$this->wikiadictos->tokenizeSequence($sequenceObj->subID, $sequenceObj->sequence, $this->session->userdata('userId'));
				} else {
					// taken
					$sequenceObj->taken = $checkSequence;
				}
			}

			$this->session->set_userdata( array(
				'translated' => $sequenceObj->translated,
				'version' => $sequenceObj->version,
				'taken' => $sequenceObj->taken,
				'subId' => $sequenceObj->subID,
				'sequence' => $sequenceObj->sequence,
				'start_time' => $sequenceObj->start_time,
				'start_time_fraction' => $sequenceObj->start_time_fraction,
				'end_time' => $sequenceObj->end_time,
				'end_time_fraction' => $sequenceObj->end_time_fraction
			) );

		}
		// var_dump($sequenceObj);die();
		echo json_encode($sequenceObj);
	}

	public function untokenizeSequence() {
		// if( !is_null( $this->session->userdata('savedCurrent') ) && !$this->session->userdata('savedCurrent') ) {
			$this->wikiadictos->untokenizeSequence($this->session->userdata('subId'), $this->session->userdata('sequence'));
			// $this->session->unset_userdata('savedCurrent');
		// }
	}

	public function saveSequence() {
		$translation = $this->trimTranslation($this->input->post('translation'));
		if( !is_null($translation) && $this->validateTranslation($translation) ){
			$userToken = $this->wikiadictos->checkTokenUser( $this->session->userdata('subId'), $this->session->userdata('sequence') );

			$data = array(
				'subID' => $this->session->userdata('subId'),
				'sequence' => $this->session->userdata('sequence'),
				'authorID' => $this->session->userdata('userId'),
				'version' => $this->session->userdata('version') + 1,
				'original' => 0,
				'locked' => 0,
				'in_date' => date("Y-m-d H:i:s"),
				'start_time' => $this->session->userdata('start_time'),
				'start_time_fraction' => $this->session->userdata('start_time_fraction'),
				'end_time' => $this->session->userdata('end_time'),
				'end_time_fraction' => $this->session->userdata('end_time_fraction'),
				'text' => $translation,
				'lang_id' => 4,
				'edited_seq' => $this->session->userdata('sequence'),
				'last' => 1,
				'estart_time' => $this->session->userdata('start_time'),
				'estart_time_fraction' => $this->session->userdata('start_time_fraction'),
				'eend_time' => $this->session->userdata('end_time'),
				'eend_time_fraction' => $this->session->userdata('end_time_fraction'),
				'fversion' => 0,
				'tested' => 0
			);

			// if( $this->session->userdata('translated') && is_null($userToken) ) {
				// UPDATE
				// $error = 'update';
				// $data = array(
				// 	''
				// );
				$this->wikiadictos->unLast( $data['subID'], $data['sequence'] );
			// }

			// } elseif( !$this->session->userdata('translated') && $userToken == $this->session->userdata('userId') ) {
				// INSERT
				if( $this->wikiadictos->saveSequence($data) ) {
					$error = null;
					$this->log_activity();
					$this->session->set_userdata( array('version' => $this->session->userdata('version') + 1 ) );
				} else {
					$error = 'Error intentando guardar la secuencia.';
					$this->log_activity(1);
				}
			// } else $error = 'no se pudo crear una traducción nueva ni actualizar la existente';
		} else $error = 'Fallo en la validación de la traducción.';
		echo json_encode( ['error' => $error] );
	}

	private function log_activity($error=0) {
		$data = array(
			'user_id' => $this->session->userdata('userId'),
			'sub_id' => $this->session->userdata('subId'),
			'sequence' => $this->session->userdata('sequence'),
			'version' => $this->session->userdata('version') + 1,
			'error' => $error,
			'skipped' => substr($this->session->userdata('skipped'), 1)
		);
		$this->wikiadictos->log_activity($data);
		$this->session->set_userdata(array('skipped' => ''));
	}

	public function validateTranslation($translation) {
		$valid = true;
		$lines = explode("\n", $translation);
		if (count($lines) > 2) $valid = false;
		else {
			foreach ($lines as $line) {
				if(mb_strlen($line) > 43) $valid = false;
			}
		}

		return $valid;
	}

	public function trimTranslation($translation) {
		$lines = explode("\n", $translation);
		$translation = '';
		foreach ($lines as $line) {
			$translation .= trim($line)."\n";
		}
		return trim($translation);
	}
	
	public function test() {
		echo json_encode(["error"=>0]);
		// print_r(date('Y-m-d H:i:s'));
		// print_r( date("Y-m-d H:i:s", strtotime('-3 hours')) );
		// var_dump($this->wikiadictos->freeLockedSequences());
		// $this->wikiadictos->getSubtitles();
		// print_r($this->subtitlesPendings);
		// print_r($this->wikiadictos->getFileName(167));
		// print_r($this->wikiadictos->getSequence(178,382));
		// print_r($this->db->last_query());
	}
}
?>