<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Autocomplete_model extends BF_model{
	
	protected $table_name   = 'autocomplete';
	protected $key          = 'id';
	
	public function __construct() {
		
	}
	
	public function autocomplete($query = null) {
		$this->select('name');
		if($query) {
			$this->like('name', $query);
		}
		$result = $this->find_all();
		return $result;
	}
	
	public function insert_name($name) {
		$arr = array('name' => $name);
		$result = $this->insert($arr);
		return $result;
	}
}