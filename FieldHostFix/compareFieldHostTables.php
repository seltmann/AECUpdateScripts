<?php
#compare old fieldhost table with new one. Check to see if ColEventUID, HostNumber and string are the same. If update date should be the same as update date of old fieldhost table

require_once("MDB2.php");
require_once("DB.php");
require_once("../connection.php");


 
// === Main database connection and error handling ===
$DB =& MDB2::connect($dsn);
if (PEAR::isError($DB)) { handleError($DB->getMessage()); }

#make an output file
$fp = fopen('OLDFieldHostComparison.txt', 'w');
$output = '';
#import oldFieldHost.tsv file
#FieldHostUID	ColEventUID	HostNumber	FieldIdent	FieldNotes	CreateDate	UpdateDate	CreatedBy	UpdatedBy	TripUID	HostF	HostG	HostSp	HostSSp	HostAuthor	HostDetBy	HerbID	history

if (($handle = fopen("OLDFieldHost.txt", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
	$FieldHostUID = trim($data[0]);
	$ColEventUID = trim($data[1]);
	$HostNumber = trim($data[2]);
	$FieldIdent = trim($data[3]);
	$FieldNotes= trim($data[4]);
	$CreateDate= trim($data[5]);
	$UpdateDate= trim($data[6]);
	$CreatedBy= trim($data[7]);
	$UpdatedBy= trim($data[8]);
	$TripUID= trim($data[9]);
	$HostF= trim($data[10]);
	$HostG= trim($data[11]);
	$HostSp= trim($data[12]);
	$HostSSp= trim($data[13]);
	$HostAuthor= trim($data[14]);
	$HostDetBy= trim($data[15]);
	$HerbID= trim($data[16]);
	$history= trim($data[17]);
	
	print $FieldHostUID . "\n";
	
	//compare other informaton like CreateDate, UpdateDate,CreatedBy,UpdatedBy
	//compare parsed history with family, genus, species
	$sameFieldHostInformationValue = sameFieldHostInformation($FieldHostUID,$ColEventUID,$HostNumber,$HerbID,$HostDetBy);
	$sameCreateValues = sameCreate($FieldHostUID,$CreateDate,$CreatedBy);
	$sameUpdateValues = sameUpdate($FieldHostUID,$UpdateDate,$UpdatedBy);
	$sameTaxonName = sameTaxon($FieldHostUID,$history,$HostSSp,$HostF,$HostG,$HostSp);
	$sameAuthor = sameAuthor($FieldHostUID,$history,$HostSSp,$HostAuthor,$HostSp);
//	foreach($sameTaxonName as $n){$n;}

				$output .= $FieldHostUID . "\t" . $sameFieldHostInformationValue . "\t" . $sameCreateValues . "\t" . $sameUpdateValues . "\t" . $sameTaxonName . "\t" . $sameAuthor . "\t" . $history . "\n";
//print $output;
			}
		}
		
$header = "FieldHostUID" . "\t" . "hostUID_coleventUID_HostNumber" . "\t" . "sameCreateValues" . "\t" . "sameCreateValues" . "\t" . "sameTaxon" . "\t" . "sameAuthor" . "\t" . "history to update" . "\n" . $output;
 	fwrite($fp, $header);

function sameAuthor($FieldHostUID,$history,$HostSSp,$HostAuthor,$HostSp){
		global $DB;
		$author = explode(";",$history);
		if($HostSSp != '0' && $HostAuthor != '0'){
			$authorString = $author[4];
			$authorString = str_replace(' ', '', $authorString);
			$authorString = str_replace('.', '', $authorString);
			$authorString = trim($authorString);
			$sql = "Select FieldHostUID from FieldHost left join Flora_MNL on FieldHost.HostSSp=Flora_MNL.HostMNLUID where replace(replace(trim(Flora_MNL.HostAuthor),' ',''),'.','')='$authorString' and FieldHost.FieldHostUID='$FieldHostUID';";
		}elseif($HostSSp == '0' && $HostSp != '0' && $HostAuthor != '0'){
			$authorString = $author[3];
			$authorString = str_replace(' ', '', $authorString);
			$authorString = str_replace('.', '', $authorString);
			$authorString = trim($authorString);
			$sql = "Select FieldHostUID from FieldHost left join Flora_MNL on FieldHost.HostSp=Flora_MNL.HostMNLUID where replace(replace(trim(Flora_MNL.HostAuthor),' ',''),'.','')='$authorString' and FieldHost.FieldHostUID='$FieldHostUID';";
		}else{
			$authorString = 'No Author on Old Field Host table';
			return $authorString;
		}
		
	$result = $DB->query($sql);
	    if (PEAR::isError($result)) { die("DB Error - sameAuthor" . $results->getMessage()); }
	if ($DB->getOption('result_buffering')) {
	    return $result->numRows();}
//	return $authorString;
	}

function sameTaxon($FieldHostUID,$history,$HostSSp,$HostF,$HostG,$HostSp){
		global $DB;
		$taxonString = explode(";",$history);
		if($HostF != '0'){$F = $taxonString[0];}else{$F = '0';}	
		if($HostG != '0'){$G = $taxonString[1];}else{$G = '0';}
		if($HostSp != '0'){$Sp = $taxonString[2];}else{$Sp = '0';}					
		if($HostSSp != '0'){$SSp = $taxonString[3];}else{$SSp = '0';}
		// $F . " " . $G . " " . $Sp . " " . $SSp;
	if($SSp != '0'){	
			$sql = "select FieldHost.FieldHostUID from FieldHost left join Flora_MNL F1 on FieldHost.HostF=F1.HostMNLUID left join  Flora_MNL F2 on FieldHost.HostG=F2.HostMNLUID  left join Flora_MNL F3 on FieldHost.HostSp=F3.HostMNLUID left join  Flora_MNL F4 on FieldHost.HostSSp=F4.HostMNLUID where F1.HostTaxName='$F' and F2.HostTaxName='$G' and F3.HostTaxName='$Sp' and F4.HostTaxName='$SSp' and FieldHost.FieldHostUID='$FieldHostUID'";
	}elseif($SSp == '0'){
			$sql = "select FieldHost.FieldHostUID from FieldHost left join Flora_MNL F1 on FieldHost.HostF=F1.HostMNLUID left join  Flora_MNL F2 on FieldHost.HostG=F2.HostMNLUID  left join Flora_MNL F3 on FieldHost.HostSp=F3.HostMNLUID where F1.HostTaxName='$F' and F2.HostTaxName='$G' and F3.HostTaxName='$Sp' and FieldHost.FieldHostUID='$FieldHostUID'";
		}else{
			return "no find";
			break;
		}
		
		$result = $DB->query($sql);
	    if (PEAR::isError($result)) { die("DB Error - sameTaxon" . $results->getMessage()); }
	if ($DB->getOption('result_buffering')) {
	    return $result->numRows();}
	}



//check to see if update information is the same
function sameUpdate($FieldHostUID,$UpdateDate,$UpdatedBy){
	global $DB;
			$sql = "Select FieldHostUID from FieldHost where FieldHostUID='$FieldHostUID' and UpdateDate='$UpdateDate' and UpdatedBy='$UpdatedBy'";
	$result = $DB->query($sql);
    if (PEAR::isError($result)) { die("DB Error - sameUpdate" . $results->getMessage()); }
	if ($DB->getOption('result_buffering')) {
	    return $result->numRows();}
	}

//check to see if create date information is the same
function sameCreate($FieldHostUID,$CreateDate,$CreatedBy){
	global $DB;
			$sql = "Select FieldHostUID from FieldHost where FieldHostUID='$FieldHostUID' and CreateDate='$CreateDate' and CreatedBy='$CreatedBy'";
		//	print $sql;
	$result = $DB->query($sql);
    if (PEAR::isError($result)) { die("DB Error - sameCreate" . $results->getMessage()); }
	if ($DB->getOption('result_buffering')) {
	    return $result->numRows();}
	}

#select count if it is a match, if not return "basic information not found"
function sameFieldHostInformation($FieldHostUID,$ColEventUID,$HostNumber,$HerbID,$HostDetBy){
	global $DB;
			$sql = "Select FieldHostUID from FieldHost where FieldHostUID='$FieldHostUID' and ColEventUID='$ColEventUID' and trim(HostNumber)='$HostNumber' and (trim(HerbID)='$HerbID' or HerbID is NULL) and (trim(HostDetBy)='$HostDetBy' or HostDetBy is NULL)";
	$result = $DB->query($sql);
    if (PEAR::isError($result)) { die("DB Error - sameFieldHostInformationValue" . $results->getMessage()); }
	if ($DB->getOption('result_buffering')) {
	    return $result->numRows();}
	}
	

?>

