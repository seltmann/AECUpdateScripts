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
if (($handle = fopen("/Library/Webserver/Documents/staging_pbi/pbi-hack/data/tribes.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$value = genera_find($data[0]);
		
		while ($row =& $value->fetchRow()){
			$all_species = species($row[0]);
				while ($r =& $all_species->fetchRow()){
			echo $row[1];
			echo " ";
			echo $r[0];
			echo "<br />";
				}
			}

		}
		

		
    # Close the File.
    fclose($handle);
}


function genera_find($USI){
	global $DB;
	$sql = "select MNLUID, TaxName from MNL where ParentID='$USI'";
	$results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
	return $results;
	
}

function species($name){
	global $DB;
    $sql = "SELECT TaxName from MNL where ParentID='$name'";
    $rows = $DB->query($sql);
    if (PEAR::isError($rows)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
    	return $rows;
}
$DB->disconnect();

?>
</html>