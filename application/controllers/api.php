<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'/libraries/REST_Controller.php';

class api extends REST_Controller
{
	public function drug_get() {
		if(!$this->get('name')) {
			$this->response('no name provided', 403);
		}
		$name = $this->get('name');
		$names = $this->fda->getNames($name);
		$name = $names['generic_name'];
		$name = str_replace(' ', '+', $name);
		$this->load->model('drugs_model');
		$this->load->library('fda');
		
		$result = $this->drugs_model->get_drug($name);
		if(!empty($result)) {
			$drug_array = $this->do_structure($result);			
			$this->response($drug_array, 200);
		} else {
			//this drug wasn't found, search for it
			$this->fda->getDrug($names);
			$result = $this->drugs_model->get_drug($name);
			$drug_array = $this->do_structure($result);
			$this->response($drug_array, 200);
		}
	}
	
	public function do_structure($arr) {
		$drug_array = array();
		foreach ($arr as $row) {
			$generic_name = $row->generic_name;
			$generic_name = strtolower($generic_name);
			$generic_name = str_replace('+', ' ', $generic_name);
			$drug_array['drug']['genericName'] = $generic_name;
			$drug_array['drug']['brandNames'] = json_decode($row->brand_name, true);
			$drug_array['drug']['adverseEffects'][] = array('event'=>$row->name,'alternatives'=>$row->alternatives,'altName'=>$row->alt_name,'count'=>$row->fda_count,'maleCount'=>$row->fda_count_male,'femaleCount'=>$row->fda_count_female);
		}
		return $drug_array;
	}
	
	public function autocomplete_get() {
		$name = null;
		if(null !== $this->uri->segment(2)) {
			$name = $this->uri->segment(2);
		}
		$this->load->model('autocomplete_model');
		$result = $this->autocomplete_model->autocomplete($name);
		$this->response($result, 200);
	}
	
	public function test_get() {
		$this->load->model('autocomplete_model');
		$this->autocomplete_model->trim();
	}
	
	public function xml_get() {
		$this->load->model('autocomplete_model');
		$test = $this->autocomplete_model->read_xml();
		$this->response($test, 200);
	}
}