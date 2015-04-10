<?php
# look at old field host table (pbi_localityOLD) for host structure based on taxon names (not ids). Compare that structure with Flora_MNL table in pbi_locality for missmatches in host author names. 

#start with creating a index file based on old fieldhost table.

require_once("MDB2.php");
require_once("DB.php");
require_once("../connection.php");


 
// === Main database connection and error handling ===
$DB =& MDB2::connect($dsnOLD);
if (PEAR::isError($DB)) { handleError($DB->getMessage()); }

#make an output file
$fp = fopen('oldFieldHost.tsv', 'w');
$output = '';
		$value = createFieldHost();
		#assign values based on table names; including everything in the table
				while ($row =& $value->fetchRow()){
					// $FieldHostUID = $row[0];
					// $ColEventUID = $row[1];
					// $HostNumber = $row[2];
					// $FieldIdent = $row[3]; #deleted from new table
					// $FieldNotes = $row[4];
					// $CreateDate = $row[5];
					// $UpdateDate = $row[6];
					// $CreatedBy = $row[7];
					// $UpdatedBy = $row[8];
					// $TripUID = $row[9]; #deleted from new table
					// $HostF = $row[10];
					// $HostG = $row[11];
					// $HostSp = $row[12];
					// $HostSSp = $row[13];
					// $HostAuthor = $row[14];
					// $HostDetBy = $row[15];
					// $HerbID = $row[16];
					$HostFName = $row[0];
					$HostGName = $row[1];
					$HostSpName = $row[2];
					$HostSSpName = $row[3];
					$HostAuthorName = $row[4];
					
				$output .= $HostFName . "\t" . $HostGName . "\t" . $HostSpName . "\t" .  $HostSSpName . "\t" .  $HostAuthorName . "\n";
			}
			
			
		$header = "HostFName" . "\t" . "HostGName" . "\t" .  "HostSpName" . "\t" .  "HostSSpName" . "\t" .  "HostAuthorName" . "\n" . $output;
		
		fwrite($fp, $header);


function createFieldHost(){
	global $DB;
		$resultsGetName = $DB->query("select distinct F1.HostTaxName,F2.HostTaxName,F3.HostTaxName,F4.HostTaxName,F5.HostTaxName from FieldHost left join Flora_MNL F1 on FieldHost.HostF=F1.HostMNLUID left join Flora_MNL F2 on FieldHost.HostG=F2.HostMNLUID left join Flora_MNL F3 on FieldHost.HostSp=F3.HostMNLUID left join Flora_MNL F4 on FieldHost.HostSSp=F4.HostMNLUID left join Flora_MNL F5 on FieldHost.HostAuthor=F5.HostMNLUID order by F3.HostTaxName;");
		if (PEAR::isError($resultsGetName)) {
			error_log("DB Error - Invalid query for createFieldHost");
			exit;
		}
		
		return $resultsGetName;
	}
	

?>

