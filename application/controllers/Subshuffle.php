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

		$this->subtitlesPendings = $this->wikiadictos->getSubtitlesAndPendingSequences();
	}

	public function index() {
		// print_r($this->db->last_query());
		$subtitles = $this->wikiadictos->getSubtitles();
		
		if(empty($subtitles)) $subtitles = $this->defaultMessages;
		$data['subtitles'] = $subtitles;
		$data['firstLogIn'] = $this->session->userdata('firstLogIn');
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
		$myTranslations = $this->wikiadictos->userTranslations($this->session->userdata['userId'], $loadMore);		
		if(empty($myTranslations)) $myTranslations = $this->defaultMessages;
		echo json_encode($myTranslations);
	}

	public function subtitles() {
		$subtitles = $this->wikiadictos->getSubtitles();
		if(empty($subtitles)) $subtitles = $this->defaultMessages;
		echo json_encode($subtitles);
	}

	public function subtitleSequences($subId, $loadMore = null) {
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
		}
		echo json_encode($randomSequence);
	}
	
	public function getSequence($subId,$sequence) {
		$sequenceObj = $this->wikiadictos->getSequence($subId,$sequence);
		
		if(empty($sequenceObj)) $sequenceObj = $this->defaultMessages;
		else {
			$translatedText = $this->wikiadictos->getTranslatedSequence($subId,$sequence);
			if(!empty($translatedText)) {
				$sequenceObj->text_es = $translatedText->text;
				$sequenceObj->version = $translatedText->version;
			}
			$sequenceObj->title = $this->wikiadictos->getFileName($sequenceObj->subID)->title;
			$sequenceObj->hasNext = ($this->wikiadictos->checkSequenceExistence($sequenceObj->subID,$sequenceObj->sequence+1)) ? 1 : 0;
			$sequenceObj->hasPrev = ($this->wikiadictos->checkSequenceExistence($sequenceObj->subID,$sequenceObj->sequence-1)) ? 1 : 0;
		}
		echo json_encode($sequenceObj);
	}
	
	public function test() {
		// print_r($this->wikiadictos->getRandomSequence());
		// $this->wikiadictos->getSubtitles();
		// print_r($this->subtitlesPendings);
		// print_r($this->wikiadictos->getFileName(167));
		// print_r($this->wikiadictos->getSequence(178,382));
		// print_r($this->db->last_query());
	}
}
?>