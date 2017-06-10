<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subshuffle extends CI_Controller {

	var $defaultMessages;
	var $subtitlesPendings;

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
			"noSequence" => "¿Ninguna secuencia para traducir?<br />Dudoso, probablemente sea un error. <br />Por favor reportarlo en la sección \"Reporte de bugs\".<br /> ¡Muchas gracias!"
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
		if( is_null($subId) || !is_numeric($subId) ) die('Id de subtítulo incorrecto.');
		if(!is_null($loadMore) && !is_numeric($loadMore)) die('Error de argumento.');

		$subtitleSequences = $this->wikiadictos->getSubtitleSequences($subId, $loadMore);
		if(empty($subtitleSequences)) $subtitleSequences = $this->defaultMessages;
		echo json_encode($subtitleSequences);
	}

	public function randomSequence() {
		$randomSub = rand(0, count($this->subtitlesPendings)-1);
		$randomSequence = $this->wikiadictos->getRandomSequence($this->subtitlesPendings[$randomSub]->subId, rand(1,$this->subtitlesPendings[$randomSub]->sequences));

		if(empty($randomSequence)) $randomSequence = $this->defaultMessages;
		else {
			$randomSequence->title = $this->subtitlesPendings[$randomSub]->title;
			$randomSequence->hasNext = ($this->wikiadictos->checkSequenceExistence($randomSequence->subID,$randomSequence->sequence+1)) ? 1 : 0;
			$randomSequence->hasPrev = ($this->wikiadictos->checkSequenceExistence($randomSequence->subID,$randomSequence->sequence-1)) ? 1 : 0;
		
			// print_r($randomSequence);
			// die();
			if( !is_null( $this->session->userdata('savedCurrent') ) && !$this->session->userdata('savedCurrent') ) {
				// $this->wikiadictos->untokenizeSequence($this->session->userdata('subId'), $this->session->userdata('sequence'));
			}
			$this->session->set_userdata( array(
				'savedCurrent' => false,
				'currentVersion' => null,
				'tokened' => null,
				'subId' => $randomSequence->subID,
				'sequence' => $randomSequence->sequence,
				'start_time' => $randomSequence->start_time,
				'start_time_fraction' => $randomSequence->start_time_fraction,
				'end_time' => $randomSequence->end_time,
				'end_time_fraction' => $randomSequence->end_time_fraction
			) );
			$this->wikiadictos->tokenizeSequence($randomSequence->subID, $randomSequence->sequence, $this->session->userdata('userId'));
		}
		
		// print_r( $randomSequence );
		// print_r( $this->session->userdata('sequenceuence') );
		// die();
		echo json_encode($randomSequence);
	}
	
	public function getSequence($subId = null, $sequence = null) {
		if( is_null($subId) || is_null($sequence) || !is_numeric($subId) || !is_numeric($sequence)) die('Argumentos incorrectos.');

		$sequenceObj = $this->wikiadictos->getSequence($subId,$sequence);		

		if(empty($sequenceObj)) $sequenceObj = $this->defaultMessages;
		else {
			$checkSequence = $this->wikiadictos->checkSequenceToken($subId,$sequence,$this->session->userdata('userId'));

			$sequenceObj->version = null;
			$sequenceObj->usertoken = ( $checkSequence != null ) ? $checkSequence : null;
			if( is_null($checkSequence) ) {
				$translatedText = $this->wikiadictos->getTranslatedSequence($subId,$sequence);
				if(!empty($translatedText)) {
					$sequenceObj->text_es = $translatedText->text;
					$sequenceObj->version = $translatedText->version;
				}

				if($checkSequence === 0) $this->wikiadictos->tokenizeSequence($sequenceObj->subID, $sequenceObj->sequence, $this->session->userdata('userId'));
			}


			$sequenceObj->title = $this->wikiadictos->getFileName($sequenceObj->subID)->title;
			$sequenceObj->hasNext = ($this->wikiadictos->checkSequenceExistence($sequenceObj->subID,$sequenceObj->sequence+1)) ? 1 : 0;
			$sequenceObj->hasPrev = ($this->wikiadictos->checkSequenceExistence($sequenceObj->subID,$sequenceObj->sequence-1)) ? 1 : 0;

			// if( !is_null( $this->session->userdata('savedCurrent') ) && !$this->session->userdata('savedCurrent') && $this->session->userdata('tokened') == null ) {
			// if( !$this->session->userdata('savedCurrent') && $this->session->userdata('tokened') == null ) {
				$this->wikiadictos->untokenizeSequence($this->session->userdata('subId'), $this->session->userdata('sequence'));
			// }

			$this->session->set_userdata( array(
				'savedCurrent' => false,
				'tokened' => $sequenceObj->usertoken,
				'currentVersion' => $sequenceObj->version,
				'subId' => $sequenceObj->subID,
				'sequence' => $sequenceObj->sequence,
				'start_time' => $sequenceObj->start_time,
				'start_time_fraction' => $sequenceObj->start_time_fraction,
				'end_time' => $sequenceObj->end_time,
				'end_time_fraction' => $sequenceObj->end_time_fraction
			) );

		}
		echo json_encode($sequenceObj);
	}

	public function untokenizeSequence() {
		if( !is_null( $this->session->userdata('savedCurrent') ) && !$this->session->userdata('savedCurrent') ) {
			$this->wikiadictos->untokenizeSequence($this->session->userdata('subId'), $this->session->userdata('sequence'));
			$this->session->unset_userdata('savedCurrent');
		}
	}

	public function saveSequence() {
		// locked
		// last

		// $data = array(
		// 	'subID' => $this->session->userdata('subId'),
		// 	'sequence' => $this->session->userdata('sequence'),
		// 	'authorID' => $this->session->userdata('userId'),
		// 	'version' => $this->session->userdata('version') + 1,
		// 	'original' => 0,
		// 	'locked' => 0,
		// 	'in_date' => date("Y-m-d H:i:s"),
		// 	'start_time' => $this->session->userdata('start_time'),
		// 	'start_time_fraction' => $this->session->userdata('start_time_fraction'),
		// 	'end_time' => $this->session->userdata('end_time'),
		// 	'end_time_fraction' => $this->session->userdata('end_time_fraction'),
		// 	'text' => ,
		// 	'lang_id' => 4,
		// 	'edited_seq' => $this->session->userdata('sequence'),
		// 	'last' => 1,
		// 	'estart_time' => $this->session->userdata('estart_time'),
		// 	'estart_time_fraction' => $this->session->userdata('estart_time_fraction'),
		// 	'eend_time' => $this->session->userdata('eend_time'),
		// 	'eend_time_fraction' => $this->session->userdata('eend_time_fraction'),
		// 	'fversion' => 0,
		// 	'tested' => 0
		// );

	}
	
	public function test() {
		// print_r(date('Y-m-d H:i:s'));
		// print_r( date("Y-m-d H:i:s", strtotime('-3 hours')) );
		var_dump($this->wikiadictos->freeLockedSequences());
		// $this->wikiadictos->getSubtitles();
		// print_r($this->subtitlesPendings);
		// print_r($this->wikiadictos->getFileName(167));
		// print_r($this->wikiadictos->getSequence(178,382));
		// print_r($this->db->last_query());
	}
}
?>