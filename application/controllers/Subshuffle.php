<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subshuffle extends CI_Controller {

	var $defaultMessages;

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
			"noMore" => '¡Eso es todo, amigos!'
		];

	}

	public function index() {
		// print_r($this->db->last_query());
		$this->folder->view('index');

		// if($randomSequence = $this->wikiadictos->getRandomSequence()){
		// 	$tokenized = $this->wikiadictos->tokenizeSequence(
		// 		$randomSequence->subID,
		// 		$randomSequence->fversion,
		// 		$randomSequence->sequence,
		// 		$this->session->userdata('user')
		// 	);

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

	public function test() {
		$this->folder->view('test');
	}

	public function log() {
		// $this->folder->view('test');
	}

	public function myTranslations($loadMore = null) {
		$myTranslations = $this->wikiadictos->userTranslations($this->session->userdata['userId'], $loadMore);
		
		if(empty($myTranslations)) $myTranslations = $this->defaultMessages;

		echo json_encode($myTranslations);
		// print_r($myTranslations);
		// print_r($this->db->last_query());
	}

	public function subtitles() {
		$subtitles = $this->wikiadictos->getSubtitles();
		if(empty($subtitles)) $subtitles = $this->defaultMessages;
		echo json_encode($subtitles);
	}
	
}
?>