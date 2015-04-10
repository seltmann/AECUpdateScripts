<html>
<?php
#Host family, Host genus, Host species, colevent.date, colevent.collector, colevent.id, locality.country, locality.state, locality.county, MNL.Family, MNL.genus, MNL.species


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

$File = "/Users/kseltmann/desktop/host_names.txt";
$fh = fopen($File, 'w') or die("can't open file");
	$id = '517'; //id of the host
	$getNames = get_hosts($id);
	while ($data = $getNames->fetchRow(MDB2_FETCHMODE_ASSOC)){
		//$pbiusi = $data['pbiusi'];
		$hostf = host_name($data['hostf']);
		$hostg = host_name($data['hostg']);
		$hostsp = host_name($data['hostsp']);
		$hostssp = host_name($data['hostssp']);
		$family_id = parent_name($data['subfamily']);
		$family = name($family_id);
		$genus = name($data['genus']);
		$species = name($data['species']);
		$species_id = $data['species'];
		$colevent_date = get_collecting_event_date($data['colevent_id']);
		$collector = collector($data['colevent_id']);
		$colevent_id = $data['colevent_id'];
		$lat = latitude($data['locality_id']);
		$long = longitude($data['locality_id']);
		

		//	$results = $hostf  . "\t" . $hostg  . "\t" . $hostsp . "\t" .  $hostssp  . "\t" . $family . "\t" .  $genus  . "\t" . $species  . "\t" . $collector  . "\t" . $colevent_date  . "\t" . $colevent_id . "\t" . $lat . "\t" . $long  . "\n";
	
		$other_hosts = other_hosts_same_species($species_id);
		while ($other_host_data = $other_hosts->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$other_host_family = host_name($other_host_data['other_host_family']);
			$other_host_species = host_name($other_host_data['other_host_species']);
			$other_host_genus = host_name($other_host_data['other_host_genus']);
				
				
				$results = $other_host_family  . "\t" . $other_host_genus  . "\t" . $other_host_species . "\t" . $family . "\t" .  $genus  . "\t" . $species  . "\n";
			}	
		

		echo $results;
		fwrite($fh, $results);
		
	}

function other_hosts_same_species($id){
		global $DB;
		$resultsGetName = $DB->query("select distinct HostF as other_host_family,HostG as other_host_genus, HostSp as other_host_species from Specimen where species='$id' and HostF !='0' and HostG != '517';");
		if (PEAR::isError($resultsGetName)) {
			error_log("DB Error - Invalid query for get_hosts");
			exit;
		}
		return $resultsGetName;
	}


function get_hosts($id){
	global $DB;
	$resultsGetName = $DB->query("Select distinct HostF as hostf, HostG as hostg, HostSp as hostsp, HostSSp as hostssp, SubFamily as subfamily, Genus as genus, Species as species, ColEventUID as colevent_id, Locality as locality_id from Specimen where HostG='$id'");
	if (PEAR::isError($resultsGetName)) {
		error_log("DB Error - Invalid query for get_hosts");
		exit;
	}
	return $resultsGetName;
}


function get_collecting_event_date($id){
	global $DB;
    $sql = "SELECT DateStart from colevent where ColEventUID='$id'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for get_collecting_event_date" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
	
}

function collector($id){
	global $DB;
    $sql = "SELECT Collector.CollName from Collector left join colevent on colevent.Collector=Collector.CollectorUID where ColEventUID='$id'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for collector" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
	
}

function latitude($id){
	global $DB;
    $sql = "SELECT Dlat from Locality where LocalityUID='$id'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for latitude" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
	
}

function longitude($id){
	global $DB;
    $sql = "SELECT Dlong from Locality where LocalityUID='$id'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for longitude" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
	
}

function name($id){
	global $DB;
    $sql = "SELECT TaxName from MNL where MNLUID='$id'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for name" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
}

function host_name($id){
	global $DB;
    $sql = "SELECT HostTaxName from Flora_MNL where HostMNLUID='$id'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for host_name" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
}

function parent_name($id){
	global $DB;
    $sql = "SELECT ParentID from MNL where MNLUID='$id'";
    $results = $DB->query($sql);
    if (PEAR::isError($results)) { die("DB Error - Invalid query for host_name" . $results->getMessage()); }
	$row =& $results->fetchRow();
    	return $row[0];
}

$DB->disconnect();

?>
</html>