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

# Open the File.
if (($handle = fopen("/Users/kseltmann/desktop/NHM_specimens.csv", "r")) !== FALSE) {
#echo "File Name,Family,Scientific Name,Photograph Attribution,Creator,License"; 			
#echo "<br />";
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$value = image_find($data[0]);
		
		while ($row =& $value->fetchRow()){
			$family_id = family($row[2]);
			#echo $row[0];
			#echo ",";
			echo $row[1];#.'.jpg';
			echo ",";
			#echo name($family_id);
			#echo ",";
			#echo name($row[2]);
			#echo ",";
			#echo name($row[3]);
			#echo ",";
			#echo name($row[4]) . " " .name($row[5]);
			#echo ",";
			echo $row[6];
			#echo ",";
			#echo 'American Museum of Natural History';
			#echo ",";
			#echo 'American Museum of Natural History';
			#echo ",";
			#echo 'http://creativecommons.org/licenses/by-nc/3.0/us/';
			echo "<br />";
			
			}

		}
		

		
    # Close the File.
    fclose($handle);
}


function image_find($USI){
	global $DB;
	$sql = "select SpecimenUID, PBIUSI, Subfamily, Tribe, Genus, Species, photos, Sex from Specimen where PBIUSI='$USI'";
	$results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for image_find" . $results->getMessage()); }
	return $results;
	
}

function name($name){
	global $DB;
    $sql = "SELECT TaxName from MNL where MNLUID='$name'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for name" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
}

function family($family){
	global $DB;
    $sql = "SELECT ParentID from MNL where MNLUID='$family'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for name" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
}

$DB->disconnect();

?>
</html>