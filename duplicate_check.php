<html>
<?php
#checks to see if a list of taxa are already in the database
#parent, name (TaxName, ParentID from MNL)
// === Init ===
ini_set('include_path', './includes' . PATH_SEPARATOR . ini_get("include_path"));
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
if (($handle = fopen("/Library/WebServer/Documents/pbi_staging/pbi-reporting/data/fulg_species.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$value = already_exists_MNL($data[1], $data[0]);
		
		while ($row =& $value->fetchRow()){
			echo $row[1];
			echo ",";
			echo $row[3];
			echo "<br />";
			
		}
    }
		

    # Close the File.
    fclose($handle);
}


function already_exists_MNL($parent,$name){
	global $DB;
	$sql = "select * from MNL where ParentID='$parent' and TaxName='$name'";
	$results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
		return $results;
	
}
$DB->disconnect();

?>
</html>