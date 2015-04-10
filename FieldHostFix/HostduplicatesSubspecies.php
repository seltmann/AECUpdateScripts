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
$level = 'HostSSp';
$parent = 'HostSp';
$type = 'Specimen'; #modify for FieldHost or Specimen accordingly
if (!isLoggedIn()) { HTTP::Redirect("index.php"); }
    $rsG =& S_PBI::duplicateHost($level);
 echo "$parent | $level | $level ID | number different matches <br />";
		while ($row =& $rsG->fetchRow()) {

	
	$value = S_PBI::duplicates_with_different_parents($row[0],$parent,$level);
	if ($value > 1)
	echo "|" . S_PBI::host_name($row[0]) . "|" . $row[0] . "|" . $value . "|" . S_PBI::host_parent($row[0],$level, $parent) . "<br />"; 
}
	

$DB->disconnect();

?>
</html>