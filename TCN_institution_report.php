<?php
//take an array of USIs
//print total number of specimens
//print families -> specimens
//print First Name, Last Name, email of unique users who entered those data
//Select count(*) from Specimen left join UUser on Specimen.CreatedBy=UUser.UserName where UUser.Initials like '%TTD-TCN%' and Specimen.CreateDate  between '2011-07-01' and '2013-07-01' and Specimen.PBIUSI like '%UKYL_TCN%';

//information passed to it would be USI_Prefix, date limits Start_date, End_date, description of user


require_once("MDB2.php");
require_once("connection.php")


 
// === Main database connection and error handling ===
$DB =& MDB2::connect($dsn);
if (PEAR::isError($DB)) { handleError($DB->getMessage()); }

//Report stuff
echo "TTD-TCN NUMBERS FOR ANNUAL REPORT";
$prefix_array = array("AMNH_IZC","AMNH_ENT","AMNH_PBI","UCR_ENT","UCRC_ENT","UDCC_NRI","UNSW_ENT","NCSUNCSU","UDCC_TCN","UKYL_TCN","CUIC_TCN","CSUC_TCN","CMNH_TCN","MEMU_ENT","BPBM_TCN","CSCA_TCN","UMAM_ENT","OSACOSAC","EMECEMEC","KUNHMENT","CASC_ENT","NCSU_ENT","DPI_FSCA","OMNH_RIV","UCMS_ENT","RUAC_ENT","CUIC_ENT","VTST_ENT","BMEC_ENT","DART_ENT","RMBL_ENT");
#$prefix_array = array("BMEC_ENT","DART_ENT","RMBL_ENT","AMNH_IZC");
$start_date = "2011-07-01";
$end_date = "2013-07-01";
$description = "TTD";

echo "\nglobal total for TTD-TCN project between '$start_date' and '$end_date': " . global_total($start_date,$end_date,$description);

echo "\ntotals for each TTD-TCN institution:\n";
foreach ($prefix_array as $prefix){
	$count = get_total_count($prefix,$start_date,$end_date,$description);
	echo $prefix, "\t", $count , "\n";
}

echo "\ntotals per family for each TTD-TCN institution:\n";

foreach ($prefix_array as $prefix){
	$distinct_family = get_specimen_family($prefix,$start_date,$end_date,$description);
	echo $prefix . "\n";
	while ($row =& $distinct_family->fetchRow()) {
		$family_id = $row[0];
		echo name($row[0]) . "\t" . family_count($prefix,$start_date,$end_date,$description,$row[0]) . "\n";
	}
}

function global_total($start_date,$end_date,$description){
	global $DB;
	$sql = "Select SpecimenUID from Specimen left join UUser on Specimen.CreatedBy=UUser.UserName where UUser.Initials like '%$description%' and Specimen.CreateDate between '$start_date' and '$end_date'";
	$result = $DB->query($sql);
    if (PEAR::isError($result)) { die("DB Error - get_total_count" . $results->getMessage()); }
	if ($DB->getOption('result_buffering')) {
	    return $result->numRows();}
}

function get_total_count($prefix,$start_date,$end_date,$description){
	global $DB;
	$sql = "Select SpecimenUID from Specimen left join UUser on Specimen.CreatedBy=UUser.UserName where UUser.Initials like '%$description%' and Specimen.CreateDate between '$start_date' and '$end_date' and Specimen.PBIUSI like '%$prefix%'";
	$result = $DB->query($sql);
    if (PEAR::isError($result)) { die("DB Error - get_total_count" . $results->getMessage()); }
	if ($DB->getOption('result_buffering')) {
	    return $result->numRows();}
}

function family_count($prefix,$start_date,$end_date,$description,$family_id){
	global $DB;
	$sql = "Select SpecimenUID from Specimen left join UUser on Specimen.CreatedBy=UUser.UserName left join MNL on Specimen.Subfamily=MNL.MNLUID where UUser.Initials like '%$description%' and Specimen.CreateDate between '$start_date' and '$end_date' and Specimen.PBIUSI like '%$prefix%' and MNL.ParentID='$family_id'";
	$result = $DB->query($sql);
    if (PEAR::isError($result)) { die("DB Error - get_total_count" . $result->getMessage()); }
	if ($DB->getOption('result_buffering')) {
	    return $result->numRows();}
}

function get_specimen_family($prefix,$start_date,$end_date,$description){
	global $DB;
	$sql = "Select distinct MNL.ParentID from MNL left join Specimen on Specimen.Subfamily=MNL.MNLUID left join UUser on Specimen.CreatedBy=UUser.UserName where UUser.Initials like '%$description%' and Specimen.CreateDate  between '$start_date' and '$end_date' and Specimen.PBIUSI like '%$prefix%'and MNL.MNLUID=Specimen.Subfamily";
	$result = $DB->query($sql);
    if (PEAR::isError($result)) { die("DB Error - get_specimen_family" . $result->getMessage()); }
	    return $result;
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
