<html>
<?php
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
if (!isLoggedIn()) { HTTP::Redirect("index.php"); }
    $rsG =& S_PBI::duplicateHost();
 echo "Host Families | Host Genus | HostG ID | number different genera <br />";
		while ($row =& $rsG->fetchRow()) {

	
	$value = duplicates_with_different_families($row[0]);
	if ($value > 1)
	echo "|" . host_name($row[0]) . "|" . $row[0] . "|" . $value . "|" . host_family($row[0]) . "<br />"; 
}
	
	
function duplicates_with_different_families($gen){
	global $DB;
	$sql = "select distinct HostF from Specimen where HostG = $gen";
	$results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
while ($row =& $results->fetchRow()) 
	$a[] = array($row);
	$s = count($a);
		return $s;
	}

function host_name($name){
	global $DB;
    $sql = "SELECT HostTaxName from Flora_MNL where HostMNLUID=$name";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for duplicateMNL" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
}

function host_family($fam){
	global $DB;
	$sql = "select distinct HostF from Specimen where HostG = $fam";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for host_family" . $results->getMessage()); }
	while ($row =& $results->fetchRow()) 
	 printf(host_name($row[0]) . "(" . $row[0] . "),");
	
}

$DB->disconnect();

?>
</html>