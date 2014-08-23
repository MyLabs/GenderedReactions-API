<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Autocomplete_model extends BF_model{
	
	protected $table_name   = 'autocomplete';
	protected $key          = 'id';
	
	public function __construct() {
		
	}
	
	public function autocomplete($query = null) {
		$this->select('name');
		if($query) {
			$this->like('name', $query , 'after');
		}
		$result = $this->find_all();
		return $result;
	}
	
	public function insert_name($name) {
		$arr = array('name' => $name);
		$result = $this->insert($arr);
		return $result;
	}
	
	public function trim() {
		$this->select('*');
		$results = $this->find_all();
		foreach($results as $result) {
			var_dump($result);
			$name = trim($result->name);
			$arr = array('name' => $name);
			
			$this->where('id', $result->id);
			$test = $this->update('autocomplete', $arr);
		}
	}
	
	public function read_xml() {
		$xml = simplexml_load_file(base_url() . 'assets/xml/atc-code-lx.xml');
		$drug_arr = $xml->simpleType->restriction->enumeration;
		foreach($drug_arr as $drug) {
			$english_name = $drug->annotation->documentation[1];
			$english_name = preg_replace("/[^A-Za-z0-9 ]/", '', $english_name);
			//now we have the proper english name, so save it.
			$this->db->where('name', $english_name);
			$this->db->from('autocomplete');
			if($this->db->count_all_results() == 0) {
				$arr = array('name' => $english_name);
				$result = $this->insert($arr);
				echo $result;
			}
			
		}
	}
}