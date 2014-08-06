<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tweet_search extends CI_Controller {
	
	public function __construct() {
		parent::__construct();
		// set maximum execution time to infinity
		set_time_limit(0);
		$this->load->library('twitterlib');
		$this->load->model('drugs_model');
	}

// 	public function index() {
// 		echo 'dit is de index';
// 	}
	
// 	public function search($cache = null ) {
// 		$this->twitterlib->search($cache);
// 		exit;
// 	}
	
	public function searchone($cache = null) {
		$test = $this->twitterlib->searchone($cache);
		exit;		
	}
	
	public function test_model() {
		$test = $this->drugs_model->test();
		var_dump($test);
	}
	
}