<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Autocomplete_model extends BF_model{
	
	protected $table_name   = 'autocomplete';
	protected $key          = 'id';
	
	public function __construct() {
		
	}
	
	public function autocomplete() {
		$this->select('name');
		$result = $this->find_all();
		return $result;
	}
	
	public function insert_name($name) {
		$arr = array('name' => $name);
		$result = $this->insert($arr);
		return $result;
	}
}