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
		  					$this->session->set_userdata( array('isLoggedIn' => true, 'user' => $user->username) );
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
		print_r(password_hash('oneMoreSub',PASSWORD_BCRYPT));
		die();
		$text = "Resulta\nque lo puedes freír cualquier cosa.";
		// $pattern = "/(?:.{1,10}\n)(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos)(?:\s(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos))?(?:\s.{2,15}?)(\s)(?:.{20,40})/";
		$pattern = "/(.{1,10}\n(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos)(?:\s(?:a|ante|bajo|con|de|desde|durante|en|entre|excepto|hacia|hasta|mediante|para|por|según|sin|sobre|tras|o|pero|porque|y|que|cuando|suficientemente|tan|tanto|mucho|mucha|muchos|muchas|al|ya|el|la|lo|las|los|un|una|unas|nos|les|me|te|se|del|mi|mis|sus|su|tu|tus|esa|ese|esas|esos|iba|ni|no|es|están|sea|será|has|ho|han|he|hemos|habías|había|habían|habíamos))?\s.{2,15}?)(\s)(.{20,40})/";
		// print_r(preg_match_all($pattern, $text, $matches));
		preg_match_all($pattern, $text, $matches);
		// print_r($matches);
		// echo $text;
		// echo "\n";
		$text = preg_replace_callback(
			$pattern,
			function ($matches) {
				return $matches[1]."\n".$matches[3];
			},
			$text
		);
		$text = preg_replace("/\n/", " ", $text, 1);

		echo $text;


		// $this->load->library('ocr');

		// $this->ocr->ocrCheck()
	}

	public function subextractor() {
		$this->load->view('subextractor');
	}

}
?>