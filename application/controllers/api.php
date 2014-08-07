<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'/libraries/REST_Controller.php';

class api extends REST_Controller
{
// 	public function index_get() {
// 		if(!$this->get('name')) {
// 			$this->response('noname', 403);
// 		}
// 		$this->response('dit werkt', 200);
// 	}
	
	public function drug_get() {
		if(!$this->get('name')) {
			$this->response('no name provided', 403);
		}
		$name = $this->get('name');
		$name = $this->fda->getNames($name)['generic_name'];
		$name = str_replace(' ', '+', $name);
		$this->load->model('drugs_model');
		$this->load->library('fda');
		
		$result = $this->drugs_model->get_drug($name);
		if(!empty($result)) {
			$drug_array = $this->do_structure($result);			
			$this->response($drug_array, 200);
		} else {
			//this drug wasn't found, search for it
			$this->fda->getDrug($name);
			$result = $this->drugs_model->get_drug($name);
			$drug_array = $this->do_structure($result);
			$this->response($drug_array, 200);
		}
		
	}
	
	public function do_structure($arr) {
		$drug_array = array();
		foreach ($arr as $row) {
			$drug_array['drug']['genericName'] = $row->generic_name;
			$drug_array['drug']['brandNames'] = json_decode($row->brand_name, true);
			$drug_array['drug']['adverseEffects'][] = array('event'=>$row->name,'alternatives'=>$row->alternatives,'altName'=>$row->alt_name,'count'=>$row->fda_count,'maleCount'=>$row->fda_count_male,'femaleCount'=>$row->fda_count_female);
		}
		return $drug_array;
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