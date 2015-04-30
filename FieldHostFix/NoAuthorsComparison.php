<?php
# look at old field host table (pbi_localityOLD) for host structure based on taxon names (not ids). Compare that structure with Flora_MNL table in pbi_locality for missmatches in host author names. 

#start with creating a index file based on old fieldhost table.
#could have multiple of same name with different authors in different families

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
if (($handle = fopen("oldFieldHost2.tsv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
	$HostFamilyFile = trim($data[0]);
	$HostGenusFile = trim($data[1]);
	$HostSpeciesFile = trim($data[2]);
	$HostSubSpeciesFile = trim($data[3]);
	$HostAuthorFile= trim($data[4]);
	print $HostFamilyFile . "\n";
	if ($HostSubSpeciesFile != '' || $HostSpeciesFile !=''){
		$value = sameAuthor($HostFamilyFile,$HostGenusFile,$HostSpeciesFile,$HostSubSpeciesFile,$HostAuthorFile,$HostFamilyFile);
				while ($row =& $value->fetchRow()){
					$HostMNLUID=trim($row[0]);
					$genusHostMNL=trim($row[1]);
					$speciesHostMNL=trim($row[2]);
					$subspeciesHostMNL=trim($row[3]);
					$authorHostMNL=trim($row[4]);
					$familyHostMNL=trim($row[5]);
					$output .= $HostMNLUID . "\t" . $familyHostMNL . "\t" . $genusHostMNL . "\t" . $speciesHostMNL . "\t" .  $subspeciesHostMNL . "\t" .  $authorHostMNL . "\t" .  $HostAuthorFile . "\t" . $HostFamilyFile . "\n";
						}
	}else{
		$output .=  "nothing to do" . "\n";

			}
		}
	}
			
			
		$header = "HostMNLUID" . "\t" . "familyHostMNL" . "\t" .  "genusHostMNL" . "\t" .  "speciesHostMNL" . "\t" .  "subspeciesHostMNL" . "\t" .  "authorHostMNL" . "\t" .  "oldHOstAuthor" . "\t" .  "oldHOstFamily" . "\n" . $output;
		// // 
		fwrite($fp, $header);


function sameAuthor($HostFamilyFile,$HostGenusFile,$HostSpeciesFile,$HostSubSpeciesFile,$HostAuthorFile,$HostFamilyFile){
	global $DB;
	#accounts for typing differences in author names (i.e. Br. N or Br.N etc.); not going to standardize that now.
	$HostAuthorFile = str_replace(' ', '', $HostAuthorFile);
	if ($HostSubSpeciesFile !=''){
		$sql = "Select distinct ifnull(F1.HostMNLUID,'none'), ifnull(F3.HostTaxName,'none'),ifnull(F2.HostTaxName,'none'),ifnull(F1.HostTaxName,'none'),ifnull(F1.HostAuthor,'none'),ifnull(F4.HostTaxName,'none') from Flora_MNL F1 left join Flora_MNL F2 on F1.HostParentID=F2.HostMNLUID left join Flora_MNL F3 on F2.HostParentID=F3.HostMNLUID left join Flora_MNL F4 on F3.HostParentID=F4.HostMNLUID where F1.HostTaxName = '$HostSubSpeciesFile' and (replace(F1.HostAuthor,' ','') !='$HostAuthorFile' or F1.HostAuthor='0' or F1.HostAuthor is NULL) and F2.HostTaxName='$HostSpeciesFile' and F3.HostTaxName='$HostGenusFile'";
	}else{
		$sql = "Select distinct F1.HostMNLUID, F2.HostTaxName, F1.HostTaxName, concat('no subspecies'),ifnull(F1.HostAuthor,'none'),F3.HostTaxName from Flora_MNL F1 left join Flora_MNL F2 on F1.HostParentID=F2.HostMNLUID left join Flora_MNL F3 on F2.HostParentID=F3.HostMNLUID where F1.HostTaxName = '$HostSpeciesFile' and (replace(F1.HostAuthor,' ','') !='$HostAuthorFile' or F1.HostAuthor='0' or F1.HostAuthor is NULL) and F2.HostTaxName='$HostGenusFile'";
	}
		$resultsGetName = $DB->query($sql);
		print $sql . "\n";
		if (PEAR::isError($resultsGetName)) {
			error_log("DB Error - Invalid query for createFieldHost");
		}
		
		return $resultsGetName;
	}
	

?>

