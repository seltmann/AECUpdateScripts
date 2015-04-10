<html>
<?php
// check name and rank in specimen for HostSp
// see if cooresponds to flora_mnl
//if not print name, rank, createdby, parent
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
$level = 'HostSSp';
$parent = 'HostSp';
$specimen_level = 'SSp';

$type = 'Specimen'; #modify for FieldHost or Specimen accordingly
if (!isLoggedIn()) { HTTP::Redirect("index.php"); }
    $rsG =& S_PBI::getSpecimens($parent,$level);
		echo "parent's dont match <br />";
//gets array from Flora_MNL assocated with a HostMNLUID (coming from Specimen)
		while ($row =& $rsG->fetchRow()) {
			#echo "$parent:" . $row[0] . "," . "$level:" . $row[1] . "<br />";
			$flora_mnl_parent = S_PBI::returnParent($row[0], $row[1], $specimen_level);
			if ($flora_mnl_parent != $row[0]) {echo "" . ("$parent," . $row[0] . ",$level," . $row[1]) . "<br />"; }
}
	

$DB->disconnect();

?>
</html>