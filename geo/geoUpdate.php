<?php
#file definitions
#headers: LocalityUID	LocalityStr	town	state	skipped	uncertainty	remarks	Dlat	Dlong	UpdatedBy	UpDateDate
#windows formatted txt file from excel

#prints the hiearchy of a image; returns SpecimenUID, PBIUSI, Subfamily, Tribe, Genus, Species
// === Init ===
ini_set('include_path', '../../includes' . PATH_SEPARATOR . ini_get("include_path"));
require_once("init.php");
require_once("MDB2.php");

// === Main ===
$DB =& MDB2::connect($CONFIG['root']['DB']['dsn']);
if (PEAR::isError($DB)) { handleError($DB->getMessage()); }


# Open the File.
if (($handle = fopen("/Library/Webserver/Documents/pbi-git/pbi-specimen-ui/pbi-specimen-ui/scripts/geo/to_run/Oregon07142014.txt", "r")) !== FALSE) {
#echo "File Name,Family,Scientific Name,Photograph Attribution,Creator,License"; 			
    while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
	//print exact rows from spreadsheet
	$row = $data[0] . "\t" . $data[1] . "\t". $data[2] . "\t" . $data[3] . "\t" . $data[4] . "\t" . $data[5] . "\t" . $data[6] . "\t" . $data[7] . "\t" . $data[8] . "\t" . $data[9] . "\t" . $data[10];
			
	#create variables from used rows
			$LocalityUID = trim($data[0]);
			$LocalityStr = trim($data[1]);
			$town = trim($data[2]);
			$state = trim($data[3]);
			$skipped = trim($data[4]);
			$uncertainty = trim($data[5]);
			$remarks = trim($data[6]);
			$notes = get_notes($LocalityUID);
			$Dlat = trim($data[7]);
			$Dlong = trim($data[8]);
			$UpdatedBy = trim($data[9]);
			$UpDateDate = trim($data[10]); #must be 2013-10-13 format
			$GeoRange = georange(trim($data[5]));
		
		$check_updatedBy = updatedBy($LocalityUID,$UpdatedBy,$UpDateDate);
		$value = check_locality($LocalityUID,$LocalityStr);
		$geo_value = check_higher_geo($LocalityUID,$town,$state);
		$concat_notes_geo = concat_notes($uncertainty,$remarks,$notes,TRUE);
		$concat_notes_skip = concat_notes($uncertainty,$remarks,$notes,FALSE);
		#add check on upload notes to see if it is the same person, date and string
		if ($skipped == 'skipped' or $remarks == 'N\A'){
			if ($check_updatedBy == TRUE){
			echo 'Inserted notes only: ' . $LocalityUID . " ". $concat_notes_skip . "\n";
			update_notes($LocalityUID,$concat_notes_skip,$UpdatedBy,$UpDateDate);
			}else{
				echo 'notes already updated: '. $LocalityUID . "\n";
			}
		}elseif ($value == FALSE) {
			echo 'CHECK LAT/LONG:' . $row . "\n";
		}elseif ($geo_value == FALSE) {
			echo 'CHECK HIGHER GEO:' . $row . "\n";
		}else{
			echo 'Uploaded: ' . update_locality_record($LocalityUID,$Dlat,$Dlong,$concat_notes_geo,$GeoRange,$UpdatedBy,$UpDateDate) . "\n";	
		}	

			
	}

}


	function updatedBy($LocalityUID,$UpdatedBy,$UpDateDate){
		global $DB;
		$sql = "SELECT * FROM Locality where LocalityUID='$LocalityUID' and UpdatedBy='$UpdatedBy'";
		$results = $DB->query($sql);
	    if (PEAR::isError($results)) { die("DB Error - Invalid query for updatedBy" . $results->getMessage()); }

			$row =& $results->fetchRow();
		    	$value = $row[0];
		    if (empty($value)){
					return TRUE;
				}else{
					return FALSE;
			}
	}

	function georange($error){
		#<10m,10-100,100-1000,1000-10000,Country only,Unknown
		if ($error < 10){
			$error_bin = '<10m';
		}elseif ($error >= 10 && $error < 100){
			$error_bin = '10-100';
		}elseif ($error >= 100 && $error < 1000){
			$error_bin = '100-1000';
		}elseif ($error >= 1000 && $error <= 10000){
			$error_bin = '1000-10000';
		}else{
			$error_bin = 'Unknown';	
		}
		return $error_bin;
	}
	
	
	function update_locality_record($LocalityUID,$Dlat,$Dlong,$NNotes,$GeoRange,$UpdatedBy){
		global $DB;
		$NNotes = mysql_real_escape_string($NNotes);
		$sql = "UPDATE Locality SET Dlat='$Dlat',Dlong='$Dlong',NNotes = '$NNotes',LocAccuracy='$GeoRange',UpdatedBy='$UpdatedBy',UpDateDate=NOW(),GeoRefMethod=\"GeoLocate Software\",No_Download='1' WHERE LocalityUID='$LocalityUID'";
		 $results = $DB->query($sql);//
		 if (PEAR::isError($results)) { die("DB Error - Invalid query for update_locality_record" . $results->getMessage()); }	//
		return $sql;
	}

	function update_notes($LocalityUID,$NNotes,$UpdatedBy){
			global $DB;
			$NNotes = mysql_real_escape_string($NNotes);
			$sql = "UPDATE Locality SET NNotes = '$NNotes',UpDateDate=NOW(),UpdatedBy=\"$UpdatedBy\", No_Download='1' where LocalityUID=\"$LocalityUID\"";
			$results = $DB->query($sql);
			if (PEAR::isError($results)) { die("DB Error - Invalid query for update_notes" . $results->getMessage()); }
			return $sql;	
		}

//gets old notes from db TODO add a check to see if the notes are not already there.		
	function get_notes($LocalityUID){
		global $DB;
		$sql = "SELECT NNotes FROM Locality where LocalityUID='$LocalityUID' limit 1 ";
		$results = $DB->query($sql);
	    if (PEAR::isError($results)) { die("DB Error - Invalid query for get_notes" . $results->getMessage()); }

		$row =& $results->fetchRow();
	    	$value = $row[0];
			return $value;
	}
		
//concats notes for insert
	function concat_notes($uncertainty,$remarks,$notes,$is_geo){
		if (empty($remarks)){
			$print_remarks = '';
		}else{
			$print_remarks = 'georeference remarks:' . $remarks . ";";
		}

		
		if ($is_geo == FALSE){
			$update_notes = $print_remarks . $notes;
		}else{
			$update_notes = 'uncertainty: '. $uncertainty . ' m;'. $print_remarks . $notes;
		}	
		return rtrim($update_notes,';');
	}
			
////check if Dlat and Dlong are 0.0000 && locality matches LocalityStr	
	 function check_higher_geo($LocalityUID,$town,$state){
			global $DB;
			$sql = "SELECT * FROM Locality L1 left join SubDiv SD on L1.SubDivUID=SD.SubDivUID left join StateProv SP on SD.StateProvUID=SP.StateProvUID where L1.LocalityUID='$LocalityUID' and SP.StateProv='$state' and SD.SubDivStr='$town' limit 1";
			$results = $DB->query($sql);
		    if (PEAR::isError($results)) { die("DB Error - Invalid query for check_higher_geo" . $results->getMessage()); }

			$row =& $results->fetchRow();
		    	$value = $row[0];
		    if (empty($value)){
					return FALSE;
				}else{
					return TRUE;
			}
		}		

////check if Dlat and Dlong are 0.0000 && locality matches LocalityStr		
	 function check_locality($LocalityUID,$LocalityStr){
			global $DB;
			$sql = "Select * from Locality where LocalityUID='$LocalityUID' and Dlat='0.00000' and Dlong='0.00000' and LocalityStr=\"$LocalityStr\" limit 1;";
			$results = $DB->query($sql);
		    if (PEAR::isError($results)) { die("DB Error - Invalid query for check_locality" . $results->getMessage()); }

			$row =& $results->fetchRow();
		    	$value = $row[0];
		    if (empty($value)){
					return FALSE;
				}else{
					return TRUE;
			}
		}

		
    fclose($handle);
// }



$DB->disconnect();

?>