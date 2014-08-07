<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once FCPATH . 'application/vendor/autoload.php';

class Fda {

	public function __construct() {
		$this->CI =& get_instance();
		
		$this->CI->load->model('drugs_model');
		$this->CI->load->model('side_effects_model');
		$this->CI->load->model('side_effects_lookup_model');
	}

	function sideEffectId($sideEffectName) {
		$result = mysql_query("SELECT * FROM side_effects WHERE name = '$sideEffectName'");
			if (!$result || mysql_num_rows($result) == 0) {
				$arr = array('name' => $sideEffectName);
				$test = $this->CI->side_effects_model->insert($arr);
				return mysql_insert_id();
			}
			else {
				$row = mysql_fetch_array($result);
				return $row['id'];
			}
	}

	function sideEffectLookupId($drugId, $sideEffectId) {
		$result = mysql_query("SELECT * FROM side_effects_lookup WHERE drug_id = $drugId AND effect_id = $sideEffectId");
			if (!$result || mysql_num_rows($result) == 0) {
				$arr = array('drug_id' => $drugId, 'effect_id' => $sideEffectId);
				$test = $this->CI->side_effects_lookup_model->insert($arr);
				return mysql_insert_id();
			}
			else {
				$row = mysql_fetch_array($result);
				return $row['id'];
			}
	}

	function drugId($drugName, $brandName = array()) {
		$result = mysql_query("SELECT * FROM drugs WHERE generic_name = '$drugName'");

			if (!$result || mysql_num_rows($result) == 0) {
				$arr = array('generic_name' => $drugName, 'brand_name' => json_encode($brandName));
				$test = $this->CI->drugs_model->insert($arr);
				return mysql_insert_id();
			}
			else {
				$row = mysql_fetch_array($result);
				return $row['id'];
			}
	}
	
	function getNames($name) {
		$client = new GuzzleHttp\Client();
		
		$nameUrl = "https://api.fda.gov/drug/event.json?search=patient.drug.openfda.generic_name:" . $name . "+AND+patient.patientsex:1&limit=1&api_key=HhASVaQrzWcEEDhpEZdfOPiBtVggxepGbDviSuIg";
		$brandUrl = "https://api.fda.gov/drug/event.json?search=patient.drug.openfda.brand_name:" . $name . "+AND+patient.patientsex:1&limit=1&api_key=HhASVaQrzWcEEDhpEZdfOPiBtVggxepGbDviSuIg";
		try {
			$res = $client->get($nameUrl, []);
			$array = $res->json();
			$brand_name = $array['results'][0]['patient']['drug'][0]['openfda']['brand_name'];
			$generic_name = $array['results'][0]['patient']['drug'][0]['openfda']['generic_name'];
		} catch(Exception $e) {
			try {
				$res = $client->get($brandUrl, []);
				$array = $res->json();
				$brand_name = $array['results'][0]['patient']['drug'][0]['openfda']['brand_name'];
				$generic_name = $array['results'][0]['patient']['drug'][0]['openfda']['generic_name'];
			} catch(Exception $e) {
				exit('{"error":"not found"}');
			}
		}
		return array('generic_name'=>$generic_name[0], 'brand_name'=>$brand_name);		
	}

	function connect() {
		$dbhost = '130.229.9.90';
		$dbuser = 'train';
		$dbpass = 'train';


		$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql' . mysql_error());

		mysql_select_db('gender');

		return $conn;
	}

	function getDrug($drugGenericName) {
		
		$drugNames = $this->getNames($drugGenericName);
		$drugGenericName = $drugNames['generic_name'];
		$drugGenericName = str_replace(' ', '+', $drugGenericName);
		
		$client = new GuzzleHttp\Client();

		$urlMale = "https://api.fda.gov/drug/event.json?search=patient.drug.openfda.generic_name:" . $drugGenericName . "+AND+patient.patientsex:1&count=patient.reaction.reactionmeddrapt.exact&api_key=HhASVaQrzWcEEDhpEZdfOPiBtVggxepGbDviSuIg";
		$urlFemale = "https://api.fda.gov/drug/event.json?search=patient.drug.openfda.generic_name:" . $drugGenericName . "+AND+patient.patientsex:2&count=patient.reaction.reactionmeddrapt.exact&api_key=HhASVaQrzWcEEDhpEZdfOPiBtVggxepGbDviSuIg";
		try {
			$res = $client->get($urlMale, []);
			$array = $res->json();
	        if (isset($array['error'])) {
	            $error = $array['error'];
	            if ($error['code'] == "NOT FOUND") {
	                
	            }
	        }
		} catch (Exception $e) {
			try {
				$urlMale = "https://api.fda.gov/drug/event.json?search=patient.drug.openfda.brand_name:" . $drugGenericName . "+AND+patient.patientsex:1&count=patient.reaction.reactionmeddrapt.exact&api_key=HhASVaQrzWcEEDhpEZdfOPiBtVggxepGbDviSuIg";
		        $urlFemale = "https://api.fda.gov/drug/event.json?search=patient.drug.openfda.brand_name:" . $drugGenericName . "+AND+patient.patientsex:2&count=patient.reaction.reactionmeddrapt.exact&api_key=HhASVaQrzWcEEDhpEZdfOPiBtVggxepGbDviSuIg";
	            $res = $client->get($altNameUrl, []);
	            $array = $res->json();
			} catch (Exception $e) {
				exit('{"error":"not found"}');
			}
		}

		$resultsMale = $array['results'];

		$res = $client->get($urlFemale, []);
		$array = $res->json();
		$resultsFemale = $array['results'];
		
		$drugId = $this->drugId($drugGenericName, $drugNames['brand_name']);
		
		for ($i = 0; $i < count($resultsMale); $i++) {
			$currentSideEffect = $resultsMale[$i];
			$eventCount = $currentSideEffect['count'];
			$sideEffectId = $this->sideEffectId($currentSideEffect['term']);
			$sideEffectLookupId = $this->sideEffectLookupId($drugId, $sideEffectId);

			mysql_query("UPDATE side_effects_lookup SET fda_count_male = $eventCount WHERE id = $sideEffectLookupId LIMIT 1");
		}

		for ($i = 0; $i < count($resultsFemale); $i++) {
			$currentSideEffect = $resultsFemale[$i];
			$eventCount = $currentSideEffect['count'];
			$sideEffectId = $this->sideEffectId($currentSideEffect['term']);
			$sideEffectLookupId = $this->sideEffectLookupId($drugId, $sideEffectId);

			mysql_query("UPDATE side_effects_lookup SET fda_count_female = $eventCount WHERE id = $sideEffectLookupId LIMIT 1");
		}
	}
}
