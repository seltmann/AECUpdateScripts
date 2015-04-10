<html>
<?php

#returns from list of states a list of secondary subdivisions 
# select StateProvUID from StateProv where CountryUID='8';
// === Init ===
ini_set('include_path', '../includes' . PATH_SEPARATOR . ini_get("include_path"));
require_once("init.php");
require_once("MDB2.php");
require_once("S_PBI.php");

$UserMode = "Edit";

// === Main ===
$DB =& MDB2::connect($CONFIG['root']['DB']['dsn']);
if (PEAR::isError($DB)) { handleError($DB->getMessage()); }

// Handle querystring
M_Validation::checkVars(array('UID' => 1), false);

// Check Login
session_start();

# Open the File.
if (($handle = fopen("/Library/Webserver/Documents/staging_pbi/pbi-hack/data/stateprov.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$value = county_find($data[0]);
		#var_dump($data);
		print name($data[0]);
		while ($row =& $value->fetchRow()){
			echo $row[0];
			echo ",";
			
			}

		}
		

		
    # Close the File.
    fclose($handle);
}


function county_find($UID){
	global $DB;
	$sql = " select SubDivStr from SubDiv where StateProvUID='$UID';";
	$results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for county_find" . $results->getMessage()); }
	return $results;
	
}

function name($name){
	global $DB;
    $sql = "select StateProv from StateProv where StateProvUID='$name'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for name" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return '<br /><b>' . $row[0] . '</b>,';
}
$DB->disconnect();

?>
</html>