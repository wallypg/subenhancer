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
		// SELECT `s`.`entryID` as `entryID`, `s`.`subID` as `subID`, `s`.`sequence` as `sequence`, `s`.`start_time` as `start_time`, `s`.`start_time_fraction` as `start_time_fraction`, `s`.`end_time` as `end_time`, `s`.`end_time_fraction` as `end_time_fraction`, `s`.`text` as `text`, `s`.`fversion` as `fversion` FROM `subs` `s` JOIN `translating` `t` ON `s`.`subID` = `t`.`subID` AND `s`.`fversion` = `t`.`fversion` AND `s`.`sequence` = `t`.`sequence` WHERE `tokened` =0 ORDER BY RAND() LIMIT 1
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
		return new stdClass();
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
}