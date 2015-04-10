<?php
# look at old field host table (pbi_localityOLD) for host structure based on taxon names (not ids). Compare that structure with Flora_MNL table in pbi_locality for missmatches in host author names. 

#start with creating a index file based on old fieldhost table.

require_once("MDB2.php");
require_once("DB.php");
require_once("../connection.php");


 
// === Main database connection and error handling ===
$DB =& MDB2::connect($dsn);
if (PEAR::isError($DB)) { handleError($DB->getMessage()); }

#make an output file
$fp = fopen('noAuthorsToFix.tsv', 'w');
$output = '';
#import oldFieldHost.tsv file
#for each name compare with what is in pbi_locality
if (($handle = fopen("oldFieldHost.tsv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
	$HostFamilyFile = $data[0];
	$HostGenusFile = $data[1];
	$HostSpeciesFile = $data[2];
	$HostSubSpeciesFile = $data[3];
	$HostAuthorFile= $data[4];
	print $HostFamilyFile . "\n";
	if ($HostSubSpeciesFile != '' || $HostSpeciesFile !=''){
		$value = sameAuthor($HostFamilyFile,$HostGenusFile,$HostSpeciesFile,$HostSubSpeciesFile,$HostAuthorFile);
				while ($row =& $value->fetchRow()){
					$authorHostMNL = 'Nothing';
					$HostMNLUID= 'Nothing';
					$genusHostMNL='Nothing';
					$HostMNLUID=$row[0];
					$genusHostMNL=$row[1];
					$speciesHostMNL=$row[2];
					$subspeciesHostMNL=$row[3];
					$authorHostMNL=$row[4];
					$output .= $HostMNLUID . "\t" . $genusHostMNL . "\t" . $speciesHostMNL . "\t" .  $subspeciesHostMNL . "\t" .  $authorHostMNL . "\n";
						}
	}else{
		$output .=  "nothing to do" . "\n";

			}
		}
	}
			
			
		$header = "HostMNLUID" . "\t" . "genusHostMNL" . "\t" .  "speciesHostMNL" . "\t" .  "subspeciesHostMNL" . "\t" .  "authorHostMNL" . "\n" . $output;
		// // 
		fwrite($fp, $header);


function sameAuthor($HostFamilyFile,$HostGenusFile,$HostSpeciesFile,$HostSubSpeciesFile,$HostAuthorFile){
	global $DB;
	
	if ($HostSubSpeciesFile !=''){
		$sql = "Select ifnull(F1.HostMNLUID,'none'), ifnull(F3.HostTaxName,'none'),ifnull(F2.HostTaxName,'none'),ifnull(F1.HostTaxName,'none'),ifnull(F1.HostAuthor,'none') from Flora_MNL F1 left join Flora_MNL F2 on F1.HostParentID=F2.HostMNLUID left join Flora_MNL F3 on F2.HostParentID=F3.HostMNLUID where F1.HostTaxName = '$HostSubSpeciesFile' and F1.HostAuthor !='$HostAuthorFile' and F2.HostTaxName='$HostSpeciesFile' and F3.HostTaxName='$HostGenusFile'";
	}else{
		$sql = "Select F1.HostMNLUID, F2.HostTaxName, F1.HostTaxName, concat('no subspecies'),F1.HostAuthor from Flora_MNL F1 left join Flora_MNL F2 on F1.HostParentID=F2.HostMNLUID where F1.HostTaxName = '$HostSpeciesFile' and F1.HostAuthor !='$HostAuthorFile' and F2.HostTaxName='$HostGenusFile'";
	}
		$resultsGetName = $DB->query($sql);
		print $sql;
		if (PEAR::isError($resultsGetName)) {
			error_log("DB Error - Invalid query for createFieldHost");
		}
		
		return $resultsGetName;
	}
	

?>

