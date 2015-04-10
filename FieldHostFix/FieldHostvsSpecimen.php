<html>
<?php
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

# for each of these need to change the variable below and run for each in hiearchy
# 1. child HostSSp -> parent HostSp; 
# 2. HostSp -> HostG; 
# 3. HostG -> HostF 

$parent = "HostF";
$child = "HostG";

if (!isLoggedIn()) { HTTP::Redirect("index.php"); }
    $rsG =& field_host_distinct($parent,$child);
		while ($row =& $rsG->fetchRow()) {
$FieldHostParent = $row[0];
$FieldHostChild = $row[1];

#raise flag if finds a mis-match
#not most effecient because updates for each instance of specimen; could improve		
	$value = field_host_compare_specimens($FieldHostParent,$FieldHostChild,$parent,$child);
	while ($row =& $value->fetchRow()) {
$SpecimenUID = $row[0];
$SpecimenHostParent = $row[1];
$SpecimenHostChild = $row[2];	
 	echo "SpecimenUID, " . $SpecimenUID . ",SpecimenHost". $parent . "," . "$SpecimenHostParent" . ",SpecimenHost" . $child . "," . $SpecimenHostChild . "<br />";

 #uncomment this function to run the updates
 update_SQL($SpecimenHostParent,$SpecimenHostChild,$parent,$child);
 }
}

#get an array of field host MNL ids (HostF,HostG,HostSp,HostSSp)
function field_host_distinct($parent,$child){
	global $DB;
	$sql = "select distinct $parent,$child from FieldHost where $child !='0';";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
    return $results;
}

#check array against Specimen (HostF, HostG, HostSp, HostSSP)
function field_host_compare_specimens($FieldHostParent,$FieldHostChild,$parent,$child){
	global $DB;
	$sql = "select SpecimenUID, $parent, $child from Specimen where $child = $FieldHostChild and $parent !=$FieldHostParent limit 1;";
		$results = $DB->query($sql);
	    if (PEAR::isError($results)) { die("DB Error - Invalid query for field_host_compare_specimens" . $results->getMessage()); }
	return $results;	
}

#updates the FieldHost table to match Specimen table
function update_SQL($SpecimenHostParent,$SpecimenHostChild,$parent,$child){
	global $DB;
	$sql = "update FieldHost set $parent=$SpecimenHostParent where $child=$SpecimenHostChild";
	$results = $DB->query($sql);
	if (PEAR::isError($results)) { die("DB Error - Invalid query for field_host_compare_specimens" . $results->getMessage()); }
}
 

$DB->disconnect();

?>
</html>