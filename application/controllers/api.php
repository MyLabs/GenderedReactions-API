<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'/libraries/REST_Controller.php';

class api extends REST_Controller
{
	public function index_get() {
		if(!$this->get('name')) {
			$this->response('noname', 403);
		}
		$this->response('dit werkt', 200);
	}
	
	public function drug_get() {
		if(!$this->get('name')) {
			$this->response('no name provided', 403);
		}
		$this->load->model('drugs_model');
		
		$result = $this->drugs_model->get_drug($this->get('name'));
		
		$drug_array = array();
		foreach ($result as $row) {
			$drug_array['drug']['genericName'] = $row->generic_name;
			$drug_array['drug']['brandNames'] = json_decode($row->brand_name, true);
			$drug_array['drug']['adverseEffects'][] = array('event'=>$row->name,'alternatives'=>$row->alternatives,'count'=>$row->fda_count,'maleCount'=>$row->fda_count_male,'femaleCount'=>$row->fda_count_female);
		}

		$this->response($drug_array, 200);
		
	}
	
	public function drug2_get() {
		if(!$this->get('name')) {
			$this->response('no name provided', 403);
		}
		$this->load->model('drugs_model');
	
		$result = $this->drugs_model->get_drug($this->get('name'));
	
		$this->response($result, 200);
	
	}
}