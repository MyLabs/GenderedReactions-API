<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Drugs_model extends BF_Model
{
    protected $table_name   = 'drugs';
    protected $key          = 'id';
    protected $soft_deletes = FALSE;
    protected $date_format  = 'int';
    protected $log_user     = FALSE;

    protected $set_created  = TRUE;
    protected $created_field    = 'created_on';
    protected $created_by_field = 'created_by';

    protected $set_modified     = FALSE;
    protected $modified_field   = 'modified_on';
    protected $modified_by_field = 'modified_by';

    protected $deleted_field    = 'deleted';
    protected $deleted_by_field = 'deleted_by';

    // Observers
    protected $before_insert    = array();
    protected $after_insert     = array();
    protected $before_update    = array();
    protected $after_update     = array();
    protected $before_find      = array();
    protected $after_find       = array();
    protected $before_delete    = array();
    protected $after_delete     = array();

    protected $return_insert_id = true;
    protected $return_type      = 'object';
    protected $protected_attributes = array();
    protected $field_info           = array();

    protected $validation_rules         = array();
    protected $insert_validation_rules  = array();
    protected $skip_validation          = false;
    protected $empty_validation_rules   = array();
    
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
    
    public function get_drug($name) {
// 		$this->select('drugs.*', )
// 		$this->join('side_effects_lookup', 'id = side_effects_lookup.drug_id');
// 		$result = $this->find_all();
// 		return $result;
		
		// if you want to query your primary data from the table 'tblanswers',
		$this->db->select('a.generic_name, a.brand_name,b.fda_count, b.fda_count_female,b.fda_count_male,c.name,c.alternatives');
		$this->db->from('drugs a');
		$this->db->where('a.generic_name', $name);
		$this->db->join('side_effects_lookup b', 'b.drug_id = a.id', 'left');
		$this->db->join('side_effects c', 'c.id = b.effect_id', 'left');
		$query = $this->db->get();
		return $query->result();
    }
    
}