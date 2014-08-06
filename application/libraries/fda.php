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
// 		echo 'sideeffectId';
// 		var_dump($result);
			if (!$result || mysql_num_rows($result) == 0) {
// 				$test = mysql_query("INSERT INTO side_effects VALUES ('', '$sideEffectName', '')");
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
// 		echo "Inserting new side effect lookup: <br/>";
// 		var_dump($drugId, $sideEffectId);
		$result = mysql_query("SELECT * FROM side_effects_lookup WHERE drug_id = $drugId AND effect_id = $sideEffectId");
// 		echo "SELECT * FROM side_effects_lookup WHERE drug_id = $drugId AND effect_id = $sideEffectId <br/>";
// 		var_dump($result);
			if (!$result || mysql_num_rows($result) == 0) {
// 				echo "Adding new side effect as none found <br/>";
// 				mysql_query("INSERT INTO side_effects_lookup VALUES ('', $drugId, $sideEffectId, '', '', '')");
				$arr = array('drug_id' => $drugId, 'effect_id' => $sideEffectId);
				$test = $this->CI->side_effects_lookup_model->insert($arr);
				return mysql_insert_id();
			}
			else {
				$row = mysql_fetch_array($result);
// 				echo "Using existing side effect " . $row['id'] . " name: " . $row['drug_id'];
				return $row['id'];
			}
	}

	function drugId($drugName) {
		$result = mysql_query("SELECT * FROM drugs WHERE generic_name = '$drugName'");
// 		echo "Drug ID: SELECT * FROM drugs WHERE 'generic_name' = '$drugName' <br/>";

			if (!$result || mysql_num_rows($result) == 0) {
// 				$test = mysql_query("INSERT INTO drugs VALUES ('', '$drugName', '')");
// 				$query = "INSERT INTO drugs VALUES ('', '$drugName', '')";
// 				$test = $this->db->query($query);
				$arr = array('generic_name' => $drugName);
				$test = $this->CI->drugs_model->insert($arr);
// 				echo 'inserting drug:';
				return mysql_insert_id();
			}
			else {
				$row = mysql_fetch_array($result);
				return $row['id'];
			}
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
		
// 		var_dump($this->uri->segment_array());
		
// 		$drugGenericName = end($this->uri->segment_array());
		
		$client = new GuzzleHttp\Client();

		//$urlAll = "https://api.fda.gov/drug/event.json?search=patient.drug.openfda.generic_name:" . $drugGenericName . "&count=patient.reaction.reactionmeddrapt.exact";
		$urlMale = "https://api.fda.gov/drug/event.json?search=patient.drug.openfda.generic_name:" . $drugGenericName . "+AND+patient.patientsex:1&count=patient.reaction.reactionmeddrapt.exact";
		$urlFemale = "https://api.fda.gov/drug/event.json?search=patient.drug.openfda.generic_name:" . $drugGenericName . "+AND+patient.patientsex:2&count=patient.reaction.reactionmeddrapt.exact";

		$res = $client->get($urlMale, []);
		$array = $res->json();
		$resultsMale = $array['results'];

		$res = $client->get($urlFemale, []);
		$array = $res->json();
		$resultsFemale = $array['results'];
		
		$drugId = $this->drugId($drugGenericName);
		
		for ($i = 0; $i < count($resultsMale); $i++) {
			$currentSideEffect = $resultsMale[$i];
			$eventCount = $currentSideEffect['count'];
			$sideEffectId = $this->sideEffectId($currentSideEffect['term']);
			$sideEffectLookupId = $this->sideEffectLookupId($drugId, $sideEffectId);

			mysql_query("UPDATE side_effects_lookup SET fda_count_male = $eventCount WHERE id = $sideEffectLookupId LIMIT 1");
// 			echo "SQL QUERY: UPDATE side_effects_lookup SET fda_count_male = $eventCount WHERE id = $sideEffectLookupId LIMIT 1 <br/>";
		}

		for ($i = 0; $i < count($resultsFemale); $i++) {
			$currentSideEffect = $resultsFemale[$i];
			$eventCount = $currentSideEffect['count'];
			$sideEffectId = $this->sideEffectId($currentSideEffect['term']);
			$sideEffectLookupId = $this->sideEffectLookupId($drugId, $sideEffectId);

			mysql_query("UPDATE side_effects_lookup SET fda_count_female = $eventCount WHERE id = $sideEffectLookupId LIMIT 1");
// 			echo "SQL QUERY: UPDATE side_effects_lookup SET fda_count_female = $eventCount WHERE id = $sideEffectLookupId LIMIT 1 <br/>";
		}
	}
}
