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

	public function updateLoginInfo($userId) {
		$this->db->set('last', date('Y-m-d H:i:s'));
		// $this->db->set('navegate', 'subshuffle/login');
		$this->db->where('userID', $userId);
		$this->db->update('users');
	}

	public function getSequence($subId, $fversion, $sequence) {
		$this->db->select("
			entryID as entryID,
			subID as subID,
			sequence as sequence,
			start_time as start_time,
			start_time_fraction as start_time_fraction,
			end_time as end_time,
			end_time_fraction as end_time_fraction,
			text as text,
			fversion as fversion
		");
		$this->db->where(array(
			'subID' => $subId,
			'fversion' => $fversion,
			'sequence' => $sequence
		));
		$query = $this->db->get('subs');

		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		return false;
	}

	public function setSequence($id) {

	}

	// check fversion
	public function userTranslations($userId, $loadMore) {
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
			version,
			fversion
		");
		$this->db->join("files","fs.subID = files.subID");
		$this->db->where(array(
			'authorID' => $userId,
			'original' => 0,
			'lang_id' => 4
		));
		$this->db->where("version = (SELECT MAX(version) FROM subs ss  WHERE ss.sequence = fs.sequence AND ss.subID = fs.subID)");
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

	public function getRandomSequence() {
		// Puede traer líneas de subtítulos cerrados
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
		$this->db->order_by('entryID','RANDOM');
		$this->db->limit(1);
		$this->db->where('tokened', 0);
		$query = $this->db->get('subs s');

		if($query->num_rows() > 0)
		{
			return $query->row();
		}
		return [];
	}

	public function getShowsOld() {
		// Get shows that have pending sequences to translate
		$this->db->distinct();
		$this->db->select("
			sh.title as title,
			s.subID as subId
		");
		$this->db->join("translating t","s.subID = t.subID AND s.fversion = t.fversion AND s.sequence = t.sequence");
		$this->db->join("files f","f.subID = s.subID");
		$this->db->join("shows sh","sh.showID = f.showID");
		$this->db->where('tokened', 0);
		$query = $this->db->get('subs s');

		if($query->num_rows() > 0)
		{
			return $query->result();
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
		if(!is_null($loadMore)) $this->db->where('entryID >',$loadMore);
		$this->db->where('s.subID', $subId);
		$query = $this->db->get('subs s');

		if($query->num_rows() > 0)
		{
			return $query->result();
		}
		return [];

	}

	public function tokenizeSequence($subId, $fversion, $sequence, $userId) {
		$this->db->trans_start();
		$this->db->set('tokened', 1);
		$this->db->set('userID', $userId);
		$this->db->where(array(
			'subID' => $subId,
			'fversion' => $fversion,
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
	public function untokenizeSequence() {

	}


	public function showName() {

	}
}