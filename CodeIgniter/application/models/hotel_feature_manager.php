<?php

class Hotel_Feature_Manager extends CI_Model {

	function __construct() {
		parent::__construct();
		include APPPATH . 'models/hotel_feature.php';
	}

	function new_hotel_feature($attr) {
		return new Hotel_Feature($attr);
	}

	function get_hotels($feature_id) {
		$sql = 'SELECT hotel_code FROM hotel_feature WHERE feature_id=' . $feature_id;
		$query = $this->db->query($sql);
		$this->load->model('Hotel_manager');
		$hotels = array();
		foreach($query->result_array() as $row) {
			array_push($hotels, $this->Hotel_manager->get_hotel($row['hotel_code']));
		}
		return $hotels;
	}

	function get_features($hotel_code) {
		$sql = 'SELECT feature_id FROM hotel_feature WHERE hotel_code=\'' . $hotel_code . '\'';
		$query = $this->db->query($sql);
		$this->load->models('Hotel_manager');
		$features = array();
		foreach($query->result_array() as $row) {
			array_push($features, Feature_manager->get_feature($row['feature_id']));
		}
		return $features;
	}

	function remove_features($hotel_code) {
		$sql = 'DELETE FROM hotel_feature WHERE hotel_code=\'' . $hotel_code . '\'';
		$query = $this->db->query($sql);
	}

	function get_all_hotel_features() {
		$sql = 'SELECT * FROM hotel_feature';
		$query = $this->db->query($sql);
		$hotel_features = array();		
		
		foreach($query->result_array() as $row) {
			array_push($hotel_features, new Hotel_Feature($row));
		}
		return $hotel_features;
	}
}

?>
