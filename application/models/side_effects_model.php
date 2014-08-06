<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Side_effects_model extends BF_Model
{
    protected $table_name   = 'side_effects';
    protected $key          = 'id';
    /** The constructor 
     *  
     */
    public function __construct() {
    }
    
    public function test() {
    	$this->select('');
    	$result = $this->find_all();
    	return $result;
    }
}