<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Booking_Controller extends CI_Controller {

	public $bookings_info = null;	

	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->library('form_validation');
		$this->load->helper('url');
		$this->load->model('Booking_manager');
		$this->load->model('Room_booking_manager');
		$this->load->model('Hotel_manager');
		$this->load->model('Room_manager');
	}

	function create_booking() {
		$this->form_validation->set_rules('start_date', 'Start Date', 'required');		
		$this->form_validation->set_rules('end_date', 'End Date', 'required');
		$this->form_validation->set_rules('location', 'Location', 'required');
	
		if($this->form_validation->run() == false) {
			$this->data['location'] = array(
				'name' => 'location',
				'id' => 'location',
				'type' => 'text',
				'placeholder' => 'please specify location',
				'value' => $this->form_validation->set_value('location'),
			);
			$this->data['start_date'] = array(
				'name' => 'start_date',
				'id' => 'start_date',
				'type' => 'text',
				'placeholder' => 'click to choose date',
				'value' => $this->form_validation->set_value('start_date'),
			);
			$this->data['end_date'] = array(
				'name' => 'end_date',
				'id' => 'end_date',
				'type' => 'text',
				'placeholder' => 'click to choose date',
				'value' => $this->form_validation->set_value('end_date'),
			);
			$this->data['num_child'] = array(
				'name' => 'num_child',
				'id' => 'num_child',
				'type' => 'text',
			);
			$this->data['num_adult'] = array(
				'name' => 'num_adult',
				'id' => 'num_adult',
				'type' => 'text',
			);
			$this->data['room_type_options'] = array(
				'double' => 'Double',
				'twin' => 'Twin',
				'single' => 'Single',
			);
			$this->data['num_room_options'] = array(
				'1' => 1,
				'2' => 2,
				'3' => 3,
				'4' => 4,
				'5' => 5,
			);
			$this->data['booking_submit'] = array(
				'name' => 'booking_submit',
				'id' => 'booking_submit',
				'value' => 'Search',
			);
			$this->load->view('booking/create_booking', $this->data);
		} else {
			redirect('booking/hotel_list', 'refresh');
		}	
	}

	function booking_list() {
		$this->load->model('Booking_manager');
		$this->data['bookings'] = $this->Booking_manager->get_all_bookings();
		$this->load->view('templates/admin/header.php');
		$this->load->view('booking/booking_list', $this->data);
	}

	function payment($hotel_code) {
		$this->load->model('Room_manager');
		$start_date = $this->session->userdata('start_date');
		$end_date = $this->session->userdata('end_date');
		$this->bookings_info = array(
			'hotel_code' => $hotel_code,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'data' => array(),
		);
		$total = 0;
		$amount = $_POST['amount'];
		$comfort_level = $_POST['comfort'];
		$type = $_POST['type'];
		$price = $_POST['price'];
		for($i=0; $i<count($amount); $i++) {
			if ($amount[$i] != "0") {
				$data = array(
					'amount' => $amount[$i],
					'comfort_level' => $comfort_level[$i],
					'type' => $type[$i],
					'price' => $price[$i]
				);
				array_push($this->bookings_info['data'], $data);
				$total += $price[$i] * $data['amount'];
			}
		}
		$view_data['start_date'] = $start_date;
		$view_data['end_date'] = $end_date;
		$view_data['total'] = $total;
		$view_data['rooms'] = $this->bookings_info['data'];
		$this->session->set_userdata('bookings_info', $this->bookings_info);
		$this->load->view('templates/header.php');
		$this->load->view('pages/payment', $view_data);
		$this->load->view('templates/footer.php');
	}

	function complete() {
		$this->load->model('Booking_manager');
		$this->load->model('Room_manager');
		$this->load->model('Room_booking_manager');
		$this->load->model('Booking');
		$this->load->model('Room_booking');
		
		$this->bookings_info = $this->session->userdata('bookings_info');
		$hotel_code = $this->bookings_info['hotel_code'];
		$start_date = date_create_from_format('d/m/Y', $this->bookings_info['start_date'])->format('Y-m-d');
		$end_date = date_create_from_format('d/m/Y', $this->bookings_info['end_date'])->format('Y-m-d');
		$booking = new Booking(array(
			'start_date' => $start_date,
			'end_date' => $end_date,
			'customer' => $this->session->userdata('user_id'),
			'num_child' => 0,
			'num_adult' => 0,
		));
		$booking->save();

		foreach($this->bookings_info['data'] as $booking_data) {
			$comfort_level = $booking_data['comfort_level'];
			$type= $booking_data['type'];
			$price = $booking_data['price'];
			$rooms = $this->Room_manager->get_available_rooms_by_group($hotel_code, $start_date, $end_date, $type, $comfort_level, $price);
		for($i=0; $i<$booking_data['amount']; $i++) {
				$room = $rooms[$i];
				$room_booking = new Room_booking(array(
					'room_code' => $room['room_code'],
					'hotel_code' => $room['hotel_code'],
					'booking_id' => $booking->id,
				));
				$room_booking->save();
			}
		}	

		$this->load->view('templates/header.php');
		$this->load->view('pages/complete');
		$this->load->view('templates/footer.php');
	}

	function delete_booking($booking_id) {
		$booking = $this->Booking_manager->get_booking($booking_id);
		$booking->delete();

		$this->data['booking'] = $booking;
		if ($booking != NULL) {
			$this->load->view('templates/admin/header.php');
			$this->load->view('booking/delete_booking_result', $this->data);
		}
	}

	function mybooking_delete($booking_id) {
		$booking = $this->Booking_manager->get_booking($booking_id);
		$booking->delete();

		$this->data['booking'] = $booking;
		if ($booking != NULL) {
			$this->load->view('templates/header.php');
			$this->load->view('booking/mybooking_delete_result', $this->data);
		}
	}

	function update_booking($booking_id) {
		$this->data['booking_id'] = $booking_id;
		$booking = $this->Booking_manager->get_booking($booking_id);
		$this->load->view('templates/admin/header.php');
		$this->load->view('booking/update_booking_result.php', $this->data);
	}

	function mybooking() {
		$booking_id = $_POST['booking_id'];
		$this->data['booking_id'] = $booking_id;
		$booking = $this->Booking_manager->get_booking($booking_id);
		if ($booking->id) {
			$rooms_booking = $this->Room_booking_manager->get_room_booking(NULL, NULL, $booking_id);

			$this->data['booking'] = $booking;
			$hotel_code = $rooms_booking[0]->hotel_code;
			$hotel = $this->Hotel_manager->get_hotel($hotel_code);
			$this->data['hotel'] = $hotel;

			$rooms = array();
			foreach ($rooms_booking as $row) {
				$room = $this->Room_manager->get_room($row->room_code, $row->hotel_code);
				array_push($rooms, $room[0]);
			}
			$this->data['rooms'] = $rooms;
		} else {
			$this->data['booking'] = NULL;
		}
		$this->load->view('templates/header.php');
		$this->load->view('booking/mybooking.php', $this->data);
	}
}	
