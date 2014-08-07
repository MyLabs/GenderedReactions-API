<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Drugs_model extends BF_Model
{
    protected $table_name   = 'drugs';
    protected $key          = 'id';
    
    /** The constructor 
     *  
     */
    public function __construct() {
    	
    }
    
    public function get_drug($name) {
// 		$this->select('drugs.*', )
// 		$this->join('side_effects_lookup', 'id = side_effects_lookup.drug_id');
// 		$result = $this->find_all();
// 		return $result;
		
		// if you want to query your primary data from the table 'tblanswers',
		$this->db->select('a.generic_name, a.brand_name,b.fda_count, b.fda_count_female,b.fda_count_male,c.name,c.alternatives,c.alt_name');
		$this->db->from('drugs a');
		$this->db->where('a.generic_name', $name);
		$this->db->join('side_effects_lookup b', 'b.drug_id = a.id', 'left');
		$this->db->join('side_effects c', 'c.id = b.effect_id', 'left');
		$query = $this->db->get();
		return $query->result();
    }
    
}