<html>
<?php
//script produces a tab delimited text file of all children of a given family whose names were recently added to the database not by user Seltmann, Katja (acting admin)
/*+----------------+---------------------+------+-----+---------+----------------+
| Field          | Type                | Null | Key | Default | Extra          |
+----------------+---------------------+------+-----+---------+----------------+
| MNLUID         | int(11)             | NO   | PRI | NULL    | auto_increment |
| TaxName        | varchar(50)         | NO   | MUL |         |                |
| TaxLevel       | varchar(15)         | NO   | MUL |         |                |
| ParentID       | int(11)             | NO   | MUL | 0       |                |
| AddedTaxFlag   | int(11)             | NO   |     | 0       |                |
| AuthorDate     | varchar(4)          | YES  |     | NULL    |                |
| CreateDate     | date                | YES  |     | NULL    |                |
| UpdateDate     | date                | YES  |     | NULL    |                |
| AuthorName     | varchar(50)         | YES  |     | NULL    |                |
| TaxLevelNumber | int(11)             | YES  |     | NULL    |                |
| CreatedBy      | varchar(50)         | YES  |     | NULL    |                |
| UpdatedBy      | varchar(50)         | YES  |     | NULL    |                |
| Insect_ID      | tinyint(3) unsigned | YES  |     | NULL    |                |
+----------------+---------------------+------+-----+---------+----------------+ 

report rows:
date name entered in db, identifer of name, name, rank, identifier of parent, parent name, person who entered, updated by, update date
*/
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

$File = "/Users/kseltmann/desktop/cicadellidae_names.txt";
$fh = fopen($File, 'w') or die("can't open file");


$Family = "Cicadellidae"; //change this and all will change

$getNames = get_mnluid_from_name($Family);
	
	//subfamily level
	while ($data = $getNames->fetchRow(MDB2_FETCHMODE_ASSOC)){
		$mnl_uid=$data['mnluid'];
		$childNames= get_children($mnl_uid);
		while ($children = $childNames->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$mnl_uid=$children['mnluid'];
			$tax_name=$children['tax_name'];
			$tax_level=$children['tax_level'];
			$parent_id=$children['parent_id'];
			$parent_name=name($children['parent_id']);
			$author_date=$children['author_date'];
			$create_date=$children['create_date'];
			$author_name=$children['author_name'];
			$created_by=$children['created_by'];
			$update_date=$children['update_date'];
			$update_by=$children['update_by'];
			if (($created_by != 'Seltmann, Katja') && ($update_by != 'Seltmann, Katja')) {
				$results = $create_date . "\t" . $mnl_uid  . "\t" . $tax_name . "\t" . $tax_level . "\t" . $parent_id  . "\t" . $parent_name . "\t" . $author_name  . "\t" . $author_date  . "\t" . $created_by . "\t" . $update_by . "\t" . $update_date . "\n";
				fwrite($fh, $results);
			} //close if
			
			$tribe= get_children($mnl_uid);
			
		//tribal level
				while ($tribe_name = $tribe->fetchRow(MDB2_FETCHMODE_ASSOC)){
					$mnl_uid=$tribe_name['mnluid'];
					$tax_name=$tribe_name['tax_name'];
					$tax_level=$tribe_name['tax_level'];
					$parent_id=$tribe_name['parent_id'];
					$parent_name=name($tribe_name['parent_id']);
					$author_date=$tribe_name['author_date'];
					$create_date=$tribe_name['create_date'];
					$author_name=$tribe_name['author_name'];
					$created_by=$tribe_name['created_by'];
					$update_date=$tribe_name['update_date'];
					$update_by=$tribe_name['update_by'];
					if (($created_by != 'Seltmann, Katja') && ($update_by != 'Seltmann, Katja')) {
						$results = $create_date . "\t" . $mnl_uid  . "\t" . $tax_name . "\t" . $tax_level . "\t" . $parent_id  . "\t" . $parent_name . "\t" . $author_name  . "\t" . $author_date  . "\t" . $created_by . "\t" . $update_by . "\t" . $update_date . "\n";
						fwrite($fh, $results);
					} //close if
					
					$genus= get_children($mnl_uid);
					
					//genus level
							while ($genus_name = $genus->fetchRow(MDB2_FETCHMODE_ASSOC)){
								$mnl_uid=$genus_name['mnluid'];
								$tax_name=$genus_name['tax_name'];
								$tax_level=$genus_name['tax_level'];
								$parent_id=$genus_name['parent_id'];
								$parent_name=name($genus_name['parent_id']);
								$author_date=$genus_name['author_date'];
								$create_date=$genus_name['create_date'];
								$author_name=$genus_name['author_name'];
								$created_by=$genus_name['created_by'];
								$update_date=$genus_name['update_date'];
								$update_by=$genus_name['update_by'];
								if (($created_by != 'Seltmann, Katja') && ($update_by != 'Seltmann, Katja')) {
									$results = $create_date . "\t" . $mnl_uid  . "\t" . $tax_name . "\t" . $tax_level . "\t" . $parent_id  . "\t" . $parent_name . "\t" . $author_name  . "\t" . $author_date  . "\t" . $created_by . "\t" . $update_by . "\t" . $update_date . "\n";
									fwrite($fh, $results);
								} //close if
								
						$species= get_children($mnl_uid);

						//genus level
								while ($species_name = $species->fetchRow(MDB2_FETCHMODE_ASSOC)){
									$mnl_uid=$species_name['mnluid'];
									$tax_name=$species_name['tax_name'];
									$tax_level=$species_name['tax_level'];
									$parent_id=$species_name['parent_id'];
									$parent_name=name($species_name['parent_id']);
									$author_date=$species_name['author_date'];
									$create_date=$species_name['create_date'];
									$author_name=$species_name['author_name'];
									$created_by=$species_name['created_by'];
									$update_date=$species_name['update_date'];
									$update_by=$species_name['update_by'];
									if (($created_by != 'Seltmann, Katja') && ($update_by != 'Seltmann, Katja')) {
										$results = $create_date . "\t" . $mnl_uid  . "\t" . $tax_name . "\t" . $tax_level . "\t" . $parent_id  . "\t" . $parent_name . "\t" . $author_name  . "\t" . $author_date  . "\t" . $created_by . "\t" . $update_by . "\t" . $update_date . "\n";
										fwrite($fh, $results);
									} //close if
					} //close  while
				} //close  while
			} //close  while
		} //close  while
	}

	
	fclose($fh);

function get_children($parent_name) { 
	global $DB;
	$resultsGetName = $DB->query("Select MNLUID as mnluid, TaxName as tax_name, TaxLevel as tax_level, ParentID as parent_id, AuthorDate as author_date, CreateDate as create_date, AuthorName as author_name, CreatedBy as created_by, UpdateDate as update_date, UpdatedBy as update_by from MNL where ParentID='$parent_name'");
	if (PEAR::isError($resultsGetName)) {
		error_log("DB Error - Invalid query for get_children");
		exit;
	}
	return $resultsGetName;
}

function get_mnluid_from_name($get_name) {
	global $DB;
	$resultsGetName = $DB->query("Select MNLUID as mnluid, TaxName as tax_name, TaxLevel as tax_level, ParentID as parent_id, AuthorDate as author_date, CreateDate as create_date, AuthorName as author_name, CreatedBy as created_by, UpdateDate as update_date, UpdatedBy as update_by from MNL where TaxName='$get_name'");
	if (PEAR::isError($resultsGetName)) {
		error_log("DB Error - Invalid query for get_mnluid_from_name");
		exit;
	}
	return $resultsGetName;
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
</html>
