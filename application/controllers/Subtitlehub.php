<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Subtitlehub extends CI_Controller {


	function __construct(){
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
	}


	public function index($goto='subenhancer') {
		
		if( isset($_GET['goto']) ) {
			$goto = urldecode($_GET['goto']);
		}

		if(isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn']) redirect(base_url().$goto);
		else {
			$data['goto'] = $goto;
			$this->load->view('login',$data);
		}
	}


	public function auth() {
		
		if($this->input->is_ajax_request()) {
			$postArray = $this->input->post();

			if( isset($postArray['username']) && isset($postArray['password']) ) {
				if (file_exists('json/users.json')) {
		  			$users = json_decode(file_get_contents('json/users.json'));
		  			foreach ($users as $user) {
		  				if($user->username == $postArray['username'] && password_verify($postArray['password'],$user->hash)) {
		  					$this->session->set_userdata( array('isLoggedIn'=>true) );
		  					echo 'true';
		  					die();
		  				}
		  			}
				}				
			}
		}
		echo 'false';
		
	}


	public function logout() {
		$this->session->unset_userdata('isLoggedIn');
		session_destroy();
		redirect(base_url());
	}

	public function test() {
		

	}
}
?>