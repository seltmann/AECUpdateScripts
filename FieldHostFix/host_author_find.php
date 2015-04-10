<html>
<?php

#returns from list of host authors that match; name_author.csv is the concat of (genus+species ids)
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
if (($handle = fopen("/Library/Webserver/Documents/staging_pbi/pbi-hack/data/name_author.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$value = author_find($data[0]);
		#var_dump($data);


		while ($row =& $value->fetchRow()){
			#var_dump($row);
			echo $data[0] . "|" . $row[0] . "|" . author_name($row[0]) . "<br />";
			
			}
		}
		

		
    # Close the File.
    fclose($handle);
}


function author_find($UID){
	global $DB;
	$sql = "Select distinct HostAuthor from FieldHost where concat(HostG,HostSp)='$UID';";
	$results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for county_find" . $results->getMessage()); }
	return $results;
	
}

function author_name($author_id){
		global $DB;
		$sql = "Select HostTaxName from Flora_MNL where HostMNLUID='$author_id';";
		$results = $DB->query($sql);
	    if (PEAR::isError($results)) { die("DB Error - Invalid query for county_find" . $results->getMessage()); }
		$row =& $results->fetchRow();
	    	return $row[0];

}

$DB->disconnect();

?>
</html>