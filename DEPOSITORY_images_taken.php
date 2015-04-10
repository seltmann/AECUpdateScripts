<html>
<?php

#prints the hiearchy of a image; returns SpecimenUID, PBIUSI, Subfamily, Tribe, Genus, Species
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
if (($handle = fopen("/Library/Webserver/Documents/staging_pbi/pbi-hack/data/specimen_images_USI.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$value = image_find($data[0]);
		
		while ($row =& $value->fetchRow()){
			echo $data[0];
			echo ",";
			echo instution($row[6]);
		//	echo ",";
		//	echo name($row[2]);
		//	echo ",";
		//	echo name($row[3]);
		//	echo ",";
		//	echo name($row[4]);
		//	echo ",";
		//	echo name($row[5]);
			echo "<br />";
			
			}

		}
		

		
    # Close the File.
    fclose($handle);
}


function image_find($USI){
	global $DB;
	$sql = "select SpecimenUID, PBIUSI, Subfamily, Tribe, Genus, Species, InstUID from Specimen where PBIUSI='$USI' and InstUID='1'";
	$results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
	return $results;
	
}

function name($name){
	global $DB;
    $sql = "SELECT TaxName from MNL where MNLUID='$name'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
}

function instution($id){
	global $DB;
    $sql = "SELECT InstCode from Institution where InstUID='$id'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
}

$DB->disconnect();

?>
</html>