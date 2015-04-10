<html>
<?php
#for first return species 626 in Flora_MNL set Parent as 553
#for second return create new abies in Flora_MNL and set with ParentID as 394
#update Specimen that has HostG 394 and HostSp 626 with new HostSp

/*Flora_MNL;    
HostMNLUID, HostTaxName, HostTaxLevel, HostParentID, AddedTaxFlag, CreateDate, UpdateDate, CreatedBy, UpdatedBy*/


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
if (($handle = fopen("/Library/WebServer/Documents/pbi_staging/pbi-reporting/data/species_insert_TEST.csv", "r")) !== FALSE) {

# data[4] is parentID for data[1]
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		host_species_parent_set($data[1], $data[4]);
		var_dump($data[1]);
		}
		

		
    # Close the File.
    fclose($handle);
}
##species_name	species_ID	number of records retrun	Genus1	GenusIDOne		Genus2	GenusIDTwo	
#sets first 
function host_species_parent_set($host,$parent){
	global $DB;
	$sql = "Update Flora_MNL set HostParentID=$parent where HostMNLUID=$host";
	$results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for host_species_parent_set" . $results->getMessage()); }
	
}

function name($name){
	global $DB;
    $sql = "SELECT TaxName from MNL where MNLUID='$name'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
}
$DB->disconnect();

?>
</html>