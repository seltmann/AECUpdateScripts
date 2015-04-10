<html>
<?php
#saved as windows csv file
# csv file columns (subfamily,tribe,genus,species,author, year) from the mirid catalog database
#checks to see if the name is in the MNL table in the same hiearchy

// === Init ===
ini_set('include_path', '../includes' . PATH_SEPARATOR . ini_get("include_path"));
require_once("init.php");
require_once("MDB2.php");
require_once("S_PBI.php");

$UserMode = "Edit";

// === Main ===
$db = mysql_connect('127.0.0.1','root','pass');



// Handle querystring
M_Validation::checkVars(array('UID' => 1), false);

// Check Login
session_start();

# Open the File.
if (($handle = fopen("/Library/Webserver/Documents/staging_pbi/pbi-hack/data/mirid.csv", "r")) !== FALSE) {
#test one; is the tribe in the right subfamily?
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$subfamily = $data[0]; //name
		$tribe = $data[1]; //name
		$genus = $data[2]; //name
		$species = $data[3]; //name
		$genus_authordate = mirid_catalog_lookup_year($genus, $db);
		$genus_authorname = mirid_catalog_lookup_author($genus, $db);
		$authordate = $data[5]; //(Author year) //need to be broken down for insert
		$authorname = $data[4];
		
			//	$test_one = parent_id($tribe, $db);
			//	$subfamily_id = name_to_id($subfamily, $db);
			//	if ($test_one != $subfamily_id) {
			//	echo "$tribe not in $subfamily <br/>";
			//		}

#test two; is the geuns in the right subfamily and does not exist at all in database; if not create insert statement
				$test_two = parent_id($genus, $db);
				$tribe_id = name_to_id($tribe, $db);
				
				if ($test_two != $tribe_id || $test_two == NULL){
				//echo "$test_two<br/>";
				//echo "$genus,$genus_authorname,$genus_authordate" . "<br />";

			echo "INSERT INTO MNL (`MNLUID`, `TaxName`, `TaxLevel`, `ParentID`, `AddedTaxFlag`, `AuthorDate`, `CreateDate`, `UpdateDate`, `AuthorName`, `TaxLevelNumber`, `CreatedBy`, `UpdatedBy`, `Insect_ID`) VALUES ('','$genus','G','$tribe_id','','$genus_authordate','2012-12-04','','$genus_authorname','4','Seltmann, Katja','','1');" . "<br/>";
					}
					//echo "test_two $test_two tribe_id $tribe_id<br/>";
					//if ($test_two == $tribe_id && $test_two != NULL){
					//echo "$genus in $tribe <br/>";
						//}
						
#test three; genus exists in database but in wrong tribe, return tribe it presently is in and where needs to move
#test four; repeat for species (1-3)
#test five; check if author/year matches database records for all names
				}
				
				

    # Close the File.
    fclose($handle);
}


function parent_id($tax_name, $db){
	mysql_select_db('pbi_locality',$db);
    $sql = "Select ParentID from MNL where TaxName='$tax_name' and TaxLevel='G'";
	$results = mysql_query($sql, $db);
	if (!$results) {
	    die('Invalid query: name' . mysql_error());
	}
	$row = mysql_fetch_row($results);
	return $row[0];
}


//will use for species; function not used for tribe
function parent_author_id($tax_name, $db){
	mysql_select_db('pbi_locality',$db);
	$sql = "Select ParentID from MNL where TaxName='$tax_name";
	//should check to see if it exists multiple times and flag if it does
		$results = mysql_query($sql, $db);
		if (!$results) {
		    die('Invalid query: parent_author_id' . mysql_error());
		}
		$row = mysql_fetch_row($results);
		return $row[0];
}


function name_to_id($name, $db){
	mysql_select_db('pbi_locality',$db);
	$sql = "Select MNLUID as mnl_uid from MNL where TaxName='$name'";
    	$results = mysql_query($sql, $db);
		if (!$results) {
		    die('Invalid query: name_to_id' . mysql_error());
		}
		$row = mysql_fetch_row($results);
		return $row[0];
}


function name($name){
	mysql_select_db('pbi_locality',$db);
    $sql = "SELECT TaxName as tax_name from MNL where MNLUID='$name'";
	$results = mysql_query($sql, $db);
	if (!$results) {
	    die('Invalid query: name' . mysql_error());
	}
	$row = mysql_fetch_row($results);
	return $row[0];
}


function mirid_catalog_lookup_year($name, $db){
	mysql_select_db('pbi',$db);
			$sql = "select id as taxon_id from taxon where name = '$name'";
			$results = mysql_query($sql, $db);
			if (!$results) {
			    die('Invalid query: mirid_catalog_lookup' . mysql_error());
			}
			$row = mysql_fetch_assoc($results);
			mysql_free_result($results);
			return year($row['taxon_id'], $db, $name);
	}
	
	
 function mirid_catalog_lookup_author($name, $db){
	
	mysql_select_db('pbi',$db);
			$sql = "select id as taxon_id from taxon where name = '$name'";
			$results = mysql_query($sql, $db);
			if (!$results) {
			    die('Invalid query: mirid_catalog_lookup' . mysql_error());
			}
			$row = mysql_fetch_assoc($results);
			mysql_free_result($results);
			return author($row['taxon_id'], $db, $name);
}

//gets the valid year from mirid catalog
function year($id, $db, $name) {
	mysql_select_db('pbi',$db);
	$a_y = mysql_query("select distinct r.recorded_name, r.page_desc, r.page_num, a.short_name as authority, bi.id as bib_inst_id, b.id as bib_id, b.year, b.subyear, b.copyright, rc.description as comment, d.name as distribution from reference r inner join synonym s on r.taxon_id = s.junior_taxon_id inner join biblio_instance bi on r.biblio_instance_id = bi.id inner join bibliography b on bi.bibliography_id = b.id inner join authority a on bi.primary_authority_id = a.id inner join ref_comment rc on rc.reference_id = r.id inner join distribution d on r.distribution_id = d.id where s.senior_taxon_id = '$id' and r.recorded_name = '$name' and s.type != 'homonym' order by b.year, b.subyear") or die(mysql_error());
	$nameRow = mysql_fetch_assoc($a_y);
	return $nameRow['year'];
}

//gets the valid author from mirid catalog
function author($id, $db, $name) {
	mysql_select_db('pbi',$db);
	$a_y = mysql_query("select distinct r.recorded_name, r.page_desc, r.page_num, a.short_name as authority, bi.id as bib_inst_id, b.id as bib_id, b.year, b.subyear, b.copyright, rc.description as comment, d.name as distribution from reference r inner join synonym s on r.taxon_id = s.junior_taxon_id inner join biblio_instance bi on r.biblio_instance_id = bi.id inner join bibliography b on bi.bibliography_id = b.id inner join authority a on bi.primary_authority_id = a.id inner join ref_comment rc on rc.reference_id = r.id inner join distribution d on r.distribution_id = d.id where s.senior_taxon_id = '$id' and r.recorded_name = '$name' and s.type != 'homonym' order by b.year, b.subyear") or die(mysql_error());
	$nameRow = mysql_fetch_assoc($a_y);
	return Trim($nameRow['authority']);
}

?>
</html>
