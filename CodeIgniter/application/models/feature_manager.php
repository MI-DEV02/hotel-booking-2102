<?php

class Feature_Manager extends CI_Model {

	function __construct() {
		parent::__construct();
		include APPPATH . 'models/feature.php';
	}

	function new_feature($attr) {
		return new Feature($attr);
	}

	function get_feature($id) {
		$sql = 'SELECT * FROM features WHERE id=' . $id;
		$query = $this->db->query($sql);
		$row = $query->row_array();
		return new Feature($row);
	}

	function get_all_features() {
		$sql = 'SELECT * FROM features';
		$query = $this->db->query($sql);
		$features = array();		
		
		foreach($query->result_array() as $row) {
			array_push($features, new Feature($row));
		}
		return $features;
	}
}

?>
