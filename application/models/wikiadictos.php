<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wikiadictos extends CI_Model 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database('wikiadictos', 'TRUE');
	}

	public function findUser($user, $password) {
		$query = $this->db->get_where('users', array('username' => $user, 'password' => $password));

		if($query->num_rows() > 0)
		{
			return $query->row()->userID;
		}
		return false;
	}

	public function firstLogIn($userId) {
		$query = $this->db->get_where("subshuffle_first_login", array("user_id" => $userId));
		
		if($query->num_rows() == 1) $return = false;
		else {
			$return = true;
			$this->db->insert('subshuffle_first_login', array('user_id' => $userId) );
		}
		return $return;
	}

	public function reportBug($userId, $report) {
		$this->db->insert('subshuffle_reports', array('user_id' => $userId, 'report' => $report) );
	}

	public function updateLoginInfo($userId) {
		$this->db->set('last', date('Y-m-d H:i:s'));
		// $this->db->set('navegate', 'subshuffle/login');
		$this->db->where('userID', $userId);
		$this->db->update('users');
	}

	public function getTranslatedSequence($subId, $sequence) {
		// **
		$this->db->select("text,version");
		$this->db->where(array(
			'subID' => $subId,
			'sequence' => $sequence,
			'original' => 0
		));
		// $this->db->where("version = (SELECT MAX(version) FROM subs ss  WHERE ss.sequence = fs.sequence AND ss.subID = fs.subID AND original = 0)");
		$this->db->where("last = 1");
		$query = $this->db->get('subs fs');

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		return [];

	}

	public function getSequence($subId, $sequence) {
		// $fversion,
		// fversion as fversion
		// 'fversion' => $fversion,
		$this->db->select("
			entryID as entryID,
			subID as subID,
			sequence as sequence,
			start_time as start_time,
			start_time_fraction as start_time_fraction,
			end_time as end_time,
			end_time_fraction as end_time_fraction,
			text as text_en
		");
		$this->db->where(array(
			'subID' => $subId,
			'sequence' => $sequence,
			'original' => 1
		));
		$query = $this->db->get('subs');

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		return [];
	}


	// public function getSequenceById($seqId) {
	// 	// $fversion,
	// 	// fversion as fversion
	// 	// 'fversion' => $fversion,
	// 	$this->db->select("
	// 		entryID as entryID,
	// 		subID as subID,
	// 		sequence as sequence,
	// 		start_time as start_time,
	// 		start_time_fraction as start_time_fraction,
	// 		end_time as end_time,
	// 		end_time_fraction as end_time_fraction,
	// 		text as text_en
	// 	");
	// 	$this->db->where(array(
	// 		'entryID' =>
	// 		'original' => 1
	// 	));
	// 	$query = $this->db->get('subs');

	// 	if($query->num_rows() == 1)
	// 	{
	// 		return $query->row();
	// 	}
	// 	return [];
	// }



	public function setSequence($id) {

	}

	// check fversion
	public function userTranslations($userId, $loadMore) {
		// fversion
		// **
		$this->db->select("
			entryID,
			fs.subID,
			title,
			sequence,
			start_time,
			start_time_fraction,
			end_time,
			end_time_fraction,
			text,
			version
		");
		$this->db->join("files","fs.subID = files.subID");
		$this->db->where(array(
			'authorID' => $userId,
			'original' => 0
		));
		// 'lang_id' => 4
		// $this->db->where("version = (SELECT MAX(version) FROM subs ss  WHERE ss.sequence = fs.sequence AND ss.subID = fs.subID)");
		$this->db->where("last = 1");
		if(!is_null($loadMore)) $this->db->where('entryID <',$loadMore);

		$this->db->order_by('in_date','DESC');
		$this->db->limit(30);
		$query = $this->db->get('subs fs');

		// print_r($this->db->last_query());
		if($query->num_rows() > 0)
		{
			return $query->result();
		}
		return [];
	}

	public function getSubtitlesAndPendingSequences() {
		// Tendría que incluir fversion
		// Get shows with pending translations
		$this->db->select("
			fi.title as title,
			fl.subID as subId,
			count(*) as sequences
		");
		$this->db->join("files fi","fi.subID = fl.subID");
		$this->db->join("translating t","t.subID = fl.subID");
		$this->db->where("fl.state <", 100);
		$this->db->group_by("fl.subID");
		$query = $this->db->get('flangs fl');

		if($query->num_rows() > 0)
		{
			return $query->result();
		}
		return [];
	}

	public function getRandomSequence($subId, $offset) {
		// s.fversion as fversion
		$this->db->select("
			s.entryID as entryID,
			s.subID as subID,
			s.sequence as sequence,
			s.start_time as start_time,
			s.start_time_fraction as start_time_fraction,
			s.end_time as end_time,
			s.end_time_fraction as end_time_fraction,
			s.text as text_en,
		");
		$this->db->join("translating t","s.subID = t.subID AND s.fversion = t.fversion AND s.sequence = t.sequence");
		$this->db->limit(1,$offset);
		$this->db->where('tokened', 0);
		$this->db->where('s.subID', $subId);
		$query = $this->db->get('subs s');
		if($query->num_rows() > 0)
		{
			return $query->row();
		}
		return [];
	}

	public function getSubtitles() {
		// Get shows that have pending sequences to translate
		$this->db->select("
			fi.title as title,
			fl.subID as subId
		");
		$this->db->join("files fi","fi.subID = fl.subID");
		$this->db->where("fl.state <", 100);
		$query = $this->db->get('flangs fl');

		if($query->num_rows() > 0)
		{
			return $query->result();
		}
		return [];
	}


	public function getSubtitleSequences($subId, $loadMore) {
		$this->db->select("
			s.entryID as entryID,
			s.subID as subID,
			s.sequence as sequence,
			s.start_time as start_time,
			s.start_time_fraction as start_time_fraction,
			s.end_time as end_time,
			s.end_time_fraction as end_time_fraction,
			s.text as text,
			s.fversion as fversion
		");
		$this->db->join("translating t","s.subID = t.subID AND s.fversion = t.fversion AND s.sequence = t.sequence");
		$this->db->order_by('s.sequence','ASC');
		$this->db->limit(30);
		$this->db->where('tokened', 0);
		if(!is_null($loadMore)) $this->db->where('s.entryID >',$loadMore);
		$this->db->where('s.subID', $subId);
		$query = $this->db->get('subs s');

		if($query->num_rows() > 0)
		{
			return $query->result();
		}
		return [];

	}

	public function tokenizeSequence($subId, $sequence, $userId) {
		// $fversion, 'fversion' => $fversion,
		$this->db->trans_start();
		$this->db->set('tokened', 1);
		$this->db->set('tokentime', date('Y-m-d H:i:s') );
		$this->db->set('userID', $userId);
		$this->db->where(array(
			'subID' => $subId,
			'sequence' => $sequence
		));
		$this->db->update('translating');
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
		    return false;
		}
		return true;
	}

	// free sequence
	public function untokenizeSequence($subId, $sequence) {
		$this->db->trans_start();
		$this->db->set('tokened', 0);
		$this->db->where(array(
			'subID' => $subId,
			'sequence' => $sequence
		));
		$this->db->update('translating');
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
		    return false;
		}
		return true;
	}

	public function unLast ($subId, $sequence) {
		$this->db->trans_start();
		$this->db->set('last', 0);
		$this->db->where(array(
			'subID' => $subId,
			'sequence' => $sequence
		));
		$this->db->update('subs');
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
		    return false;
		}
		return true;
	}

	public function checkSequenceToken($subId, $sequence, $userId) {
		// Devuelve nombre usuario, 0 o null
		$this->db->select('tokened, username');
		$this->db->where( array('subID' => $subId, 'sequence' => $sequence, 't.userID <>' => $userId) );
		$this->db->join('users u','u.userID = t.userID');
		$query = $this->db->get('translating t');
		
		if($query->num_rows() == 1) {
			$return = ($query->row()->tokened) ? $query->row()->username : 0;
		} else $return = null;
		// print_r($this->db->last_query());die();
		return $return;
	}

	public function deleteFromTranslating($subId, $sequence) {
		$this->db->trans_start();
		$this->db->where( array('subID' => $subId, 'sequence' => $sequence) );
		$this->db->delete('translating');
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
		    return false;
		}
		return true;
	}

	public function saveSequence() {

	}

	public function freeLockedSequences($userId=null) {
		$this->db->trans_start();
		$this->db->set('tokened', 0);
		// $where = "tokened = 1 AND (tokentime < '". date("Y-m-d H:i:s", strtotime('-3 hours')) ."' OR userId='". $userId ."')";
		// $this->db->where($where);
		$this->db->where(array(
			'tokened' => 1,
			'tokentime <' => date("Y-m-d H:i:s", strtotime('-3 hours'))
		));
		$this->db->update('translating');
		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
		    return false;
		}
		return true;
	}

	public function getFileName($subId) {
		$this->db->select("title as title");
		$this->db->where("subID", $subId);
		$query = $this->db->get("files");

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		return [];
	}

	public function checkSequenceExistence ($subId, $seqId) {
		$this->db->limit(1);
		$this->db->where(array(
			'subID' => $subId,
			'sequence' => $seqId
		));
		$query = $this->db->get('subs');

		if($query->num_rows() == 1)
		{
			return true;
		}
		return false;
	}

		// TRY #1
	// public function getRandomSequence() {
	// 	$this->db->select("s.entryID as entryID");
	// 	$this->db->join("translating t","s.subID = t.subID AND s.fversion = t.fversion AND s.sequence = t.sequence");
	// 	$this->db->where('tokened = 0');
	// 	$query = $this->db->get('subs s');

	// 	if($query->num_rows() > 0)
	// 	{
	// 		return array_rand($query->result());
	// 	}
	// 	return new stdClass();
	// }

	// public function getRandomSequence() {
	// 	// Puede traer líneas de subtítulos cerrados
	// 	$this->db->select("count(*)");
	// 	$this->db->join("translating t","s.subID = t.subID AND s.fversion = t.fversion AND s.sequence = t.sequence");
	// 	// $this->db->order_by('entryID','RANDOM');
	// 	// $this->db->limit(1);
	// 	$this->db->where('tokened', 0);
	// 	$query = $this->db->get('subs s');
	// 	print_r($this->db->last_query());
	// 	if($query->num_rows() > 0)
	// 	{
	// 		return $query->row();
	// 	}
	// 	return [];
	// }

	// TRY #2
	// public function getRandomSequence() {
	// 	// Puede traer líneas de subtítulos cerrados
	// 	$this->db->select("
	// 		s.entryID as entryID,
	// 		s.subID as subID,
	// 		s.sequence as sequence,
	// 		s.start_time as start_time,
	// 		s.start_time_fraction as start_time_fraction,
	// 		s.end_time as end_time,
	// 		s.end_time_fraction as end_time_fraction,
	// 		s.text as text,
	// 		s.fversion as fversion
	// 	");
	// 	$this->db->join("translating t","s.subID = t.subID AND s.fversion = t.fversion AND s.sequence = t.sequence");
	// 	$this->db->order_by('entryID','RANDOM');
	// 	$this->db->limit(1);
	// 	$this->db->where('tokened', 0);
	// 	$query = $this->db->get('subs s');
	// 	print_r($this->db->last_query());
	// 	if($query->num_rows() > 0)
	// 	{
	// 		return $query->row();
	// 	}
	// 	return [];
	// }

	// public function getShowsOld() {
	// 	// Get shows that have pending sequences to translate
	// 	$this->db->distinct();
	// 	$this->db->select("
	// 		sh.title as title,
	// 		s.subID as subId
	// 	");
	// 	$this->db->join("translating t","s.subID = t.subID AND s.fversion = t.fversion AND s.sequence = t.sequence");
	// 	$this->db->join("files f","f.subID = s.subID");
	// 	$this->db->join("shows sh","sh.showID = f.showID");
	// 	$this->db->where('tokened', 0);
	// 	$query = $this->db->get('subs s');

	// 	if($query->num_rows() > 0)
	// 	{
	// 		return $query->result();
	// 	}
	// 	return [];
	// }	
}